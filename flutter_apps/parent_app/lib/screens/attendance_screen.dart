import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';
import 'leave_request_screen.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});
  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  String _selectedMonth = 'September 2026';

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final records = api.attendance;

    final double overall = child.attendance;

    return Scaffold(
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
                          style: const TextStyle(color: Colors.white, fontSize: 38, fontWeight: FontWeight.w900),
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
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withOpacity(0.18),
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
                      color: AppTheme.orange.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.event_busy_rounded, color: AppTheme.orange, size: 22),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Need Leave for Student?', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                        Text('Submit official leave request to class teacher', style: TextStyle(fontSize: 11, color: AppTheme.grey)),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right, color: AppTheme.grey, size: 20),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Monthly Calendar Section
            SectionHeader(
              title: '📅 Monthly Calendar',
              actionLabel: _selectedMonth,
            ),
            const SizedBox(height: 12),
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  _buildCalendarGrid(),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: const [
                      _CalendarLegend(color: AppTheme.green, label: 'Present (22)'),
                      _CalendarLegend(color: AppTheme.red, label: 'Absent (1)'),
                      _CalendarLegend(color: AppTheme.orange, label: 'Leave (1)'),
                      _CalendarLegend(color: AppTheme.grey, label: 'Holiday (6)'),
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

  Widget _buildCalendarGrid() {
    final weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    // Simulated month day statuses: 1 = present, 0 = absent, 2 = leave, -1 = weekend/holiday
    final daysData = [
      1, 1, 1, 1, 1, -1, -1,
      1, 1, 1, 0, 1, -1, -1,
      1, 1, 2, 2, 1, -1, -1,
      1, 1, 1, 1, 1, -1, -1,
      1, 1,
    ];

    return Column(
      children: [
        Row(
          children: weekdays.map((w) {
            return Expanded(
              child: Center(
                child: Text(
                  w,
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.grey),
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
            final st = daysData[idx];
            Color bg = Colors.transparent;
            Color border = Colors.transparent;
            Color textCol = AppTheme.dark;

            if (st == 1) {
              bg = AppTheme.green.withOpacity(0.15);
              border = AppTheme.green.withOpacity(0.4);
              textCol = AppTheme.green;
            } else if (st == 0) {
              bg = AppTheme.red.withOpacity(0.15);
              border = AppTheme.red.withOpacity(0.4);
              textCol = AppTheme.red;
            } else if (st == 2) {
              bg = AppTheme.orange.withOpacity(0.15);
              border = AppTheme.orange.withOpacity(0.4);
              textCol = AppTheme.orange;
            } else {
              textCol = AppTheme.grey;
            }

            return Container(
              decoration: BoxDecoration(
                color: bg,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: border, width: 1),
              ),
              child: Center(
                child: Text(
                  '${idx + 1}',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: textCol),
                ),
              ),
            );
          },
        ),
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
