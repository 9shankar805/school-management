import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class BusTrackingScreen extends StatefulWidget {
  const BusTrackingScreen({super.key});
  @override
  State<BusTrackingScreen> createState() => _BusTrackingScreenState();
}

class _BusTrackingScreenState extends State<BusTrackingScreen> {
  Future<void> _makePhoneCall(String phoneNumber) async {
    final Uri launchUri = Uri(
      scheme: 'tel',
      path: phoneNumber.replaceAll(RegExp(r'\s+'), ''),
    );
    if (await canLaunchUrl(launchUri)) {
      await launchUrl(launchUri);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Could not launch dialer for $phoneNumber')),
      );
    }
  }

  Future<void> _sendSms(String phoneNumber) async {
    final Uri launchUri = Uri(
      scheme: 'sms',
      path: phoneNumber.replaceAll(RegExp(r'\s+'), ''),
    );
    if (await canLaunchUrl(launchUri)) {
      await launchUrl(launchUri);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Could not open SMS for $phoneNumber')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final bus = api.busInfo;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Live Bus GPS Tracking'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh GPS Position',
            onPressed: () {
              api.loadChildSpecificData();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Updated satellite coordinates • Live accuracy ±3m'),
                  backgroundColor: Color(0xFF1E3A8A),
                ),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Live Simulation Map Viewport
            Container(
              height: 250,
              width: double.infinity,
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.15),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Stack(
                children: [
                  // Map Background Grid & Vector Curves
                  Positioned.fill(
                    child: CustomPaint(
                      painter: _MapGridPainter(),
                    ),
                  ),

                  // Route Line & Stop Waypoints
                  const Positioned(
                    left: 40,
                    right: 40,
                    top: 100,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _WaypointDot(label: 'Gate', isPassed: true),
                        _WaypointDot(label: 'Stop 1', isPassed: true),
                        _WaypointDot(label: 'Child Stop', isPassed: false, isCurrent: true),
                        _WaypointDot(label: 'School', isPassed: false),
                      ],
                    ),
                  ),

                  // Live Bus Pulsing Marker
                  Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppTheme.primary,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 3),
                            boxShadow: [
                              BoxShadow(
                                color: AppTheme.primary.withValues(alpha: 0.5),
                                blurRadius: 20,
                                spreadRadius: 6,
                              ),
                            ],
                          ),
                          child: const Icon(Icons.directions_bus_rounded, color: Colors.white, size: 28),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black87,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white24),
                          ),
                          child: Text(
                            '${bus.busNumber} • ${bus.speedKmh.toInt()} km/h',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 11),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Live status badge overlay
                  Positioned(
                    top: 14,
                    left: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.7),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.white12),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 8,
                            height: 8,
                            decoration: const BoxDecoration(
                              color: Color(0xFF10B981),
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'GPS SATELLITE LIVE',
                            style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // ETA Card overlay
                  Positioned(
                    bottom: 14,
                    right: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF59E0B),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.3), blurRadius: 6),
                        ],
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.access_time_filled, size: 14, color: Color(0xFF0F172A)),
                          const SizedBox(width: 4),
                          Text(
                            'ETA: ${bus.eta}',
                            style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.w900, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Live speed gauge chip
                  Positioned(
                    bottom: 14,
                    left: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.65),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.speed_rounded, size: 14, color: Colors.white70),
                          const SizedBox(width: 4),
                          Text(
                            'Speed: ${bus.speedKmh.toInt()} km/h',
                            style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.w700),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn().scale(begin: const Offset(0.97, 0.97)),
            const SizedBox(height: 16),

            // Driver & Helper Contact Card
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Row(
                    children: [
                      Container(
                        width: 48,
                        height: 48,
                        decoration: const BoxDecoration(
                          color: AppTheme.primaryLight,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.person, color: AppTheme.primary, size: 26),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(bus.driverName, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                            const Text('Official Bus Captain (Verified License)', style: TextStyle(fontSize: 11, color: AppTheme.grey)),
                            Text(bus.driverPhone, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.primary)),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.phone, color: AppTheme.green),
                        tooltip: 'Call Driver',
                        onPressed: () => _makePhoneCall(bus.driverPhone),
                      ),
                      IconButton(
                        icon: const Icon(Icons.sms_outlined, color: AppTheme.primary),
                        tooltip: 'SMS Driver',
                        onPressed: () => _sendSms(bus.driverPhone),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Helper / Attendant: ${bus.helperName}', style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                      Text('Plate: ${bus.plateNumber}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.dark)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Route Progress Stepper
            const SectionHeader(title: '📍 Route Stops & Sequence'),
            const SizedBox(height: 12),
            ...bus.stops.asMap().entries.map((entry) {
              final idx = entry.key;
              final stop = entry.value;
              final isLast = idx == bus.stops.length - 1;

              return Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Column(
                    children: [
                      Container(
                        width: 22,
                        height: 22,
                        decoration: BoxDecoration(
                          color: stop.isCurrent
                              ? const Color(0xFFF59E0B)
                              : (stop.isCompleted ? AppTheme.green : AppTheme.greyLight),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: stop.isCurrent ? Colors.white : Colors.transparent,
                            width: 2,
                          ),
                          boxShadow: stop.isCurrent
                              ? [
                                  BoxShadow(
                                    color: const Color(0xFFF59E0B).withValues(alpha: 0.5),
                                    blurRadius: 8,
                                    spreadRadius: 2,
                                  ),
                                ]
                              : null,
                        ),
                        child: stop.isCompleted
                            ? const Icon(Icons.check, size: 14, color: Colors.white)
                            : (stop.isCurrent
                                ? const Icon(Icons.location_on, size: 12, color: Colors.white)
                                : null),
                      ),
                      if (!isLast)
                        Container(
                          width: 2.5,
                          height: 42,
                          color: stop.isCompleted ? AppTheme.green : AppTheme.greyLight,
                        ),
                    ],
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                stop.name,
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: stop.isCurrent ? FontWeight.w900 : FontWeight.w700,
                                  color: stop.isCurrent ? const Color(0xFFB45309) : AppTheme.dark,
                                ),
                              ),
                              Text(
                                stop.arrivalTime,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: stop.isCompleted ? AppTheme.green : AppTheme.grey,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            stop.isCurrent
                                ? 'APPROACHING STOP • 6 MINS AWAY'
                                : (stop.isCompleted ? 'Completed on time' : 'Scheduled route waypoint'),
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: stop.isCurrent ? FontWeight.w800 : FontWeight.normal,
                              color: stop.isCurrent ? const Color(0xFFB45309) : AppTheme.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            }),
          ],
        ),
      ),
    );
  }
}

class _WaypointDot extends StatelessWidget {
  final String label;
  final bool isPassed;
  final bool isCurrent;

  const _WaypointDot({
    required this.label,
    required this.isPassed,
    this.isCurrent = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: isCurrent
                ? const Color(0xFFF59E0B)
                : (isPassed ? const Color(0xFF10B981) : Colors.white24),
            border: Border.all(color: Colors.white, width: 1.5),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            fontSize: 9,
            fontWeight: FontWeight.w700,
            color: isCurrent ? const Color(0xFFF59E0B) : Colors.white70,
          ),
        ),
      ],
    );
  }
}

class _MapGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final linePaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.04)
      ..strokeWidth = 1.0;

    // Grid lines
    for (double x = 0; x < size.width; x += 30) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), linePaint);
    }
    for (double y = 0; y < size.height; y += 30) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), linePaint);
    }

    // Curving road simulation path
    final roadPaint = Paint()
      ..color = const Color(0xFF2563EB).withValues(alpha: 0.35)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 8.0
      ..strokeCap = StrokeCap.round;

    final roadPath = Path()
      ..moveTo(20, size.height * 0.8)
      ..quadraticBezierTo(size.width * 0.35, size.height * 0.2, size.width * 0.5, size.height * 0.5)
      ..quadraticBezierTo(size.width * 0.65, size.height * 0.8, size.width - 20, size.height * 0.3);

    canvas.drawPath(roadPath, roadPaint);

    final roadCenterDash = Paint()
      ..color = Colors.white.withValues(alpha: 0.7)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5;

    canvas.drawPath(roadPath, roadCenterDash);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
