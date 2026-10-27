import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class BusTrackingScreen extends StatefulWidget {
  const BusTrackingScreen({super.key});
  @override
  State<BusTrackingScreen> createState() => _BusTrackingScreenState();
}

class _BusTrackingScreenState extends State<BusTrackingScreen> {
  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final bus = api.busInfo;

    return Scaffold(
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
                const SnackBar(content: Text('Updated vehicle GPS satellite coordinates.')),
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
              height: 240,
              width: double.infinity,
              decoration: BoxDecoration(
                color: const Color(0xFF1E272E),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.12),
                    blurRadius: 18,
                    offset: const Offset(0, 6),
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
                                color: AppTheme.primary.withOpacity(0.5),
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
                        color: Colors.black.withOpacity(0.65),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 8,
                            height: 8,
                            decoration: const BoxDecoration(
                              color: AppTheme.green,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'GPS LIVE FEED',
                            style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
                          ),
                        ],
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 14,
                    right: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppTheme.accent,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Text(
                        'ETA ${bus.eta}',
                        style: const TextStyle(color: AppTheme.dark, fontWeight: FontWeight.w900, fontSize: 12),
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
                        decoration: BoxDecoration(
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
                            const Text('Official Bus Captain', style: TextStyle(fontSize: 11, color: AppTheme.grey)),
                            Text(bus.driverPhone, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.primary)),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.phone, color: AppTheme.green),
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Calling Driver at ${bus.driverPhone}...')),
                          );
                        },
                      ),
                      IconButton(
                        icon: const Icon(Icons.chat_bubble_outline, color: AppTheme.primary),
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Opening direct SMS chat with driver...')),
                          );
                        },
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Helper: ${bus.helperName}', style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                      Text('Vehicle: ${bus.plateNumber}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
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
                        width: 20,
                        height: 20,
                        decoration: BoxDecoration(
                          color: stop.isCurrent
                              ? AppTheme.accent
                              : (stop.isCompleted ? AppTheme.green : AppTheme.greyLight),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: stop.isCurrent ? Colors.white : Colors.transparent,
                            width: 2,
                          ),
                        ),
                        child: stop.isCompleted
                            ? const Icon(Icons.check, size: 12, color: Colors.white)
                            : null,
                      ),
                      if (!isLast)
                        Container(
                          width: 2,
                          height: 38,
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
                                  color: stop.isCurrent ? AppTheme.primary : AppTheme.dark,
                                ),
                              ),
                              Text(
                                stop.arrivalTime,
                                style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w700),
                              ),
                            ],
                          ),
                          Text(
                            stop.status,
                            style: TextStyle(
                              fontSize: 11,
                              color: stop.isCurrent ? AppTheme.accent : AppTheme.grey,
                              fontWeight: stop.isCurrent ? FontWeight.w800 : FontWeight.w500,
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

class _MapGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final linePaint = Paint()
      ..color = Colors.white.withOpacity(0.06)
      ..strokeWidth = 1.0;

    for (double x = 0; x < size.width; x += 30) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), linePaint);
    }
    for (double y = 0; y < size.height; y += 30) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), linePaint);
    }

    final routePaint = Paint()
      ..color = AppTheme.teal.withOpacity(0.7)
      ..strokeWidth = 4.0
      ..style = PaintingStyle.stroke;

    final path = Path();
    path.moveTo(30, size.height - 40);
    path.quadraticBezierTo(size.width * 0.4, size.height * 0.7, size.width * 0.5, size.height * 0.5);
    path.quadraticBezierTo(size.width * 0.6, size.height * 0.3, size.width - 40, 40);

    canvas.drawPath(path, routePaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
