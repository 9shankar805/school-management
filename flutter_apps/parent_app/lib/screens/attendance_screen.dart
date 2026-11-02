import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';
import 'leave_request_screen.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});
  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  String _selectedMonth = 'Bhadra 2081 (Sept 2026)';

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final records = api.attendance;

    final double overall = child.attendance;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Student Attendance'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.event_busy_outlined),
            tooltip: 'Apply for Leave',
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const LeaveRequestScreen()),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Overall Attendance Hero
            GradientCard(
              colors: const [Color(0xFF05C46B), Color(0xFF00B894)],
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          child.name,
                          style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Overall Attendance',
                          style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          '${(overall * 100).toStringAsFixed(1)}%',
                          style: const TextStyle(color: Colors.white, fontSize: 36, fontWeight: FontWeight.w900),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          overall >= 0.85 ? '✅ Excellent Standing (Above 85% min)' : '⚠️ Low attendance warning',
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    width: 84,
                    height: 84,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withValues(alpha: 0.18),
                    ),
                    child: Center(
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          CircularProgressIndicator(
                            value: overall,
                            backgroundColor: Colors.white24,
                            valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
                            strokeWidth: 8,
                          ),
                          Text(
                            '${(overall * 100).toInt()}%',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 16),

            // Quick Request Leave Action Bar
            AppCard(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const LeaveRequestScreen()),
              ),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: AppTheme.orange.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.event_busy_rounded, color: AppTheme.orange, size: 22),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Need Student Absence Leave?', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                        Text('Submit official leave request to class teacher', style: TextStyle(fontSize: 11, color: AppTheme.grey)),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right, color: AppTheme.grey, size: 20),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Monthly Calendar Section Header
            SectionHeader(
              title: '📅 Monthly Calendar',
              actionLabel: _selectedMonth,
              onAction: () => _pickMonth(context),
            ),
            const SizedBox(height: 12),
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  _buildCalendarGrid(context),
                  const SizedBox(height: 16),
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _CalendarLegend(color: AppTheme.green, label: 'Present (22)'),
                      _CalendarLegend(color: AppTheme.red, label: 'Absent (1)'),
                      _CalendarLegend(color: AppTheme.orange, label: 'Leave (1)'),
                      _CalendarLegend(color: Color(0xFF64748B), label: 'Holiday (6)'),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Subject-wise Breakdown
            const SectionHeader(title: '📊 Subject-wise Attendance'),
            const SizedBox(height: 12),
            ...records.map((rec) {
              final pct = rec.total > 0 ? (rec.present / rec.total) : 0.9;
              final color = pct >= 0.85 ? AppTheme.green : AppTheme.orange;

              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                child: AppCard(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(rec.subject, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                          Text(
                            '${rec.present} / ${rec.total} Days (${(pct * 100).toInt()}%)',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: color),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: LinearProgressIndicator(
                          value: pct,
                          backgroundColor: AppTheme.greyLight,
                          valueColor: AlwaysStoppedAnimation<Color>(color),
                          minHeight: 6,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  void _pickMonth(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            'Bhadra 2081 (Sept 2026)',
            'Shrawan 2081 (Aug 2026)',
            'Ashadh 2081 (July 2026)',
            'Jestha 2081 (June 2026)',
          ]
              .map((m) => ListTile(
                    title: Text(m, style: TextStyle(fontWeight: m == _selectedMonth ? FontWeight.bold : FontWeight.normal)),
                    trailing: m == _selectedMonth ? const Icon(Icons.check, color: AppTheme.primary) : null,
                    onTap: () {
                      setState(() => _selectedMonth = m);
                      Navigator.pop(ctx);
                    },
                  ))
              .toList(),
        ),
      ),
    );
  }

  Widget _buildCalendarGrid(BuildContext context) {
    // Nepal standard week starts with Sunday ('Sun') and Saturday ('Sat') is the weekend
    final weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // 1 = Present, 0 = Absent, 2 = Leave, -1 = Weekend (Saturday) or Public Holiday
    final daysData = [
      1, 1, 1, 1, 1, 1, -1, // Week 1 (Sat off)
      1, 1, 1, 0, 1, 1, -1, // Week 2
      1, 1, 2, 2, 1, 1, -1, // Week 3
      1, 1, 1, 1, 1, 1, -1, // Week 4
      1, 1, // Remaining
    ];

    return Column(
      children: [
        Row(
          children: weekdays.map((w) {
            final isWeekend = w == 'Sat';
            return Expanded(
              child: Center(
                child: Text(
                  w,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: isWeekend ? AppTheme.red : AppTheme.grey,
                  ),
                ),
              ),
            );
          }).toList(),
        ),
        const SizedBox(height: 10),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 7,
            mainAxisSpacing: 6,
            crossAxisSpacing: 6,
          ),
          itemCount: daysData.length,
          itemBuilder: (_, idx) {
            final dayNum = idx + 1;
            final st = daysData[idx];
            Color bg = Colors.transparent;
            Color border = Colors.transparent;
            Color textCol = AppTheme.dark;

            if (st == 1) {
              bg = AppTheme.green.withValues(alpha: 0.14);
              border = AppTheme.green.withValues(alpha: 0.4);
              textCol = AppTheme.green;
            } else if (st == 0) {
              bg = AppTheme.red.withValues(alpha: 0.14);
              border = AppTheme.red.withValues(alpha: 0.4);
              textCol = AppTheme.red;
            } else if (st == 2) {
              bg = AppTheme.orange.withValues(alpha: 0.14);
              border = AppTheme.orange.withValues(alpha: 0.4);
              textCol = AppTheme.orange;
            } else {
              textCol = const Color(0xFF94A3B8);
              bg = const Color(0xFFF1F5F9);
            }

            return InkWell(
              borderRadius: BorderRadius.circular(8),
              onTap: () => _showDayDetailSheet(context, dayNum, st),
              child: Container(
                decoration: BoxDecoration(
                  color: bg,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: border, width: 1),
                ),
                child: Center(
                  child: Text(
                    '$dayNum',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: textCol),
                  ),
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  void _showDayDetailSheet(BuildContext context, int dayNum, int status) {
    String statusTitle;
    Color statusColor;
    String statusDesc;

    if (status == 1) {
      statusTitle = 'PRESENT';
      statusColor = AppTheme.green;
      statusDesc = 'Student was present for all academic sessions.';
    } else if (status == 0) {
      statusTitle = 'UNEXCUSED ABSENT';
      statusColor = AppTheme.red;
      statusDesc = 'Student was absent without prior sanctioned leave.';
    } else if (status == 2) {
      statusTitle = 'APPROVED LEAVE';
      statusColor = AppTheme.orange;
      statusDesc = 'Parent submitted leave notice for medical reasons.';
    } else {
      statusTitle = 'WEEKEND / HOLIDAY';
      statusColor = const Color(0xFF64748B);
      statusDesc = 'School closed for scheduled weekend holiday.';
    }

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Bhadra $dayNum, 2081 B.S.',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                    ),
                    Text(
                      'September ${dayNum + 2}, 2081 AD',
                      style: const TextStyle(fontSize: 12, color: AppTheme.grey),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: statusColor),
                  ),
                  child: Text(
                    statusTitle,
                    style: TextStyle(color: statusColor, fontWeight: FontWeight.w900, fontSize: 11),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Text(statusDesc, style: const TextStyle(fontSize: 13, color: AppTheme.dark)),
            const SizedBox(height: 16),

            if (status == 1) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: const Column(
                  children: [
                    _CheckinRow(icon: Icons.login_rounded, label: 'Smart Gate In', time: '09:12 AM (Main Turnstile RFID)'),
                    Divider(height: 12),
                    _CheckinRow(icon: Icons.logout_rounded, label: 'Smart Gate Out', time: '03:45 PM (Bus Bay Gate)'),
                    Divider(height: 12),
                    _CheckinRow(icon: Icons.thermostat_outlined, label: 'Health Screener', time: '98.4°F Normal'),
                    Divider(height: 12),
                    _CheckinRow(icon: Icons.checklist_rounded, label: 'Periods Attended', time: '6 of 6 Periods'),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Close'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CheckinRow extends StatelessWidget {
  final IconData icon;
  final String label, time;

  const _CheckinRow({required this.icon, required this.label, required this.time});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppTheme.primary),
        const SizedBox(width: 8),
        Text(label, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Color(0xFF64748B))),
        const Spacer(),
        Text(time, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
      ],
    );
  }
}

class _CalendarLegend extends StatelessWidget {
  final Color color;
  final String label;
  const _CalendarLegend({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 10, color: AppTheme.grey, fontWeight: FontWeight.w600)),
      ],
    );
  }
}
