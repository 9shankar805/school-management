import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class AnalyticsScreen extends StatefulWidget {
  const AnalyticsScreen({super.key});
  @override
  State<AnalyticsScreen> createState() => _AnalyticsScreenState();
}

class _AnalyticsScreenState extends State<AnalyticsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;
  int _selectedClass = 0;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() { _tab.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Analytics & Reports'),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(icon: Icons.download_outlined, onTap: () {}, size: 38),
          const SizedBox(width: 16),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.grey,
          indicatorColor: AppTheme.primary,
          indicatorSize: TabBarIndicatorSize.label,
          tabs: const [
            Tab(text: 'Performance'),
            Tab(text: 'Attendance'),
            Tab(text: 'Assignments'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          _PerformanceTab(selectedClass: _selectedClass,
              onClassChange: (i) => setState(() => _selectedClass = i)),
          _AttendanceTab(),
          _AssignmentTab(),
        ],
      ),
    );
  }
}

class _PerformanceTab extends StatelessWidget {
  final int selectedClass;
  final ValueChanged<int> onClassChange;
  const _PerformanceTab({required this.selectedClass, required this.onClassChange});

  static const List<Map<String, dynamic>> _trend = [
    {'month': 'May', 'avg': 72.0},
    {'month': 'Jun', 'avg': 75.0},
    {'month': 'Jul', 'avg': 78.0},
    {'month': 'Aug', 'avg': 82.0},
  ];

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Summary cards
        Row(children: [
          _MetricCard(label: 'Class Avg', value: '78%',
              icon: Icons.bar_chart_rounded, color: AppTheme.primary, trend: '+4%'),
          const SizedBox(width: 12),
          _MetricCard(label: 'Top Score', value: '98%',
              icon: Icons.star_rounded, color: AppTheme.orange, trend: '+2%'),
          const SizedBox(width: 12),
          _MetricCard(label: 'Pass Rate', value: '94%',
              icon: Icons.check_circle_rounded, color: AppTheme.green, trend: '+1%'),
        ]).animate().fadeIn(),
        const SizedBox(height: 24),

        // Grade trend chart
        const SectionHeader(title: '📈 Grade Trend'),
        const SizedBox(height: 12),
        AppCard(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Average Score Over Time',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
            const SizedBox(height: 16),
            SizedBox(
              height: 120,
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: _trend.map((t) {
                  final h = (t['avg'] as double) / 100;
                  return Column(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text('${t['avg'].toInt()}%',
                          style: const TextStyle(fontSize: 10,
                              fontWeight: FontWeight.w700, color: AppTheme.primary)),
                      const SizedBox(height: 4),
                      Container(
                        width: 40,
                        height: 100 * h,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                              colors: [AppTheme.primary, AppTheme.secondary],
                              begin: Alignment.bottomCenter, end: Alignment.topCenter),
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(t['month'] as String,
                          style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    ],
                  );
                }).toList(),
              ),
            ),
          ]),
        ).animate().fadeIn(delay: 100.ms),
        const SizedBox(height: 24),

        // Grade distribution
        const SectionHeader(title: '🎯 Grade Distribution'),
        const SizedBox(height: 12),
        AppCard(
          child: Column(children: [
            _DistRow(grade: 'A+ / A', count: 4, total: 12, color: AppTheme.green),
            const SizedBox(height: 10),
            _DistRow(grade: 'B+ / B', count: 5, total: 12, color: AppTheme.blue),
            const SizedBox(height: 10),
            _DistRow(grade: 'C+ / C', count: 2, total: 12, color: AppTheme.orange),
            const SizedBox(height: 10),
            _DistRow(grade: 'D / F', count: 1, total: 12, color: AppTheme.red),
          ]),
        ).animate().fadeIn(delay: 200.ms),
        const SizedBox(height: 24),

        // At-risk students
        const SectionHeader(title: '⚠️ At-Risk Students'),
        const SizedBox(height: 12),
        ...Provider.of<TeacherProvider>(context).studentList.where((s) => s.gpa < 3.5).map((s) => AppCard(
          padding: const EdgeInsets.all(14),
          child: Row(children: [
            Container(
              width: 40, height: 40,
              decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppTheme.red.withOpacity(0.1)),
              child: Center(child: Text(s.avatar,
                  style: const TextStyle(color: AppTheme.red,
                      fontWeight: FontWeight.w800))),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(s.name, style: const TextStyle(
                  fontSize: 13, fontWeight: FontWeight.w700)),
              Text('GPA: ${s.gpa.toStringAsFixed(2)} • Attendance: ${(s.attendance * 100).toInt()}%',
                  style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
            ])),
            GradeBadge(grade: s.grade),
          ]),
        ).animate().fadeIn(delay: 300.ms)),
      ]),
    );
  }
}

class _AttendanceTab extends StatelessWidget {
  static const List<Map<String, dynamic>> _weekly = [
    {'day': 'Mon', 'pct': 0.96},
    {'day': 'Tue', 'pct': 0.88},
    {'day': 'Wed', 'pct': 0.92},
    {'day': 'Thu', 'pct': 0.84},
    {'day': 'Fri', 'pct': 0.78},
  ];

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          _MetricCard(label: 'Overall', value: '87%',
              icon: Icons.how_to_reg_rounded, color: AppTheme.green, trend: '+2%'),
          const SizedBox(width: 12),
          _MetricCard(label: 'Absent Today', value: '4',
              icon: Icons.person_off_outlined, color: AppTheme.red, trend: '-1'),
          const SizedBox(width: 12),
          _MetricCard(label: 'Late Today', value: '2',
              icon: Icons.timer_off_rounded, color: AppTheme.orange, trend: ''),
        ]).animate().fadeIn(),
        const SizedBox(height: 24),

        const SectionHeader(title: '📊 Weekly Attendance'),
        const SizedBox(height: 12),
        AppCard(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('This Week by Day',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
            const SizedBox(height: 16),
            ..._weekly.map((w) => Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: Row(children: [
                SizedBox(width: 36,
                    child: Text(w['day'] as String,
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600))),
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: LinearProgressIndicator(
                      value: w['pct'] as double,
                      backgroundColor: AppTheme.green.withOpacity(0.1),
                      valueColor: AlwaysStoppedAnimation<Color>(
                          (w['pct'] as double) >= 0.85 ? AppTheme.green : AppTheme.orange),
                      minHeight: 14,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Text('${((w['pct'] as double) * 100).toInt()}%',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              ]),
            )),
          ]),
        ).animate().fadeIn(delay: 100.ms),
        const SizedBox(height: 24),

        const SectionHeader(title: '🚨 Chronic Absentees'),
        const SizedBox(height: 12),
        ...Provider.of<TeacherProvider>(context).studentList.where((s) => s.attendance < 0.82).map((s) {
          final color = s.attendance < 0.75 ? AppTheme.red : AppTheme.orange;
          return AppCard(
            padding: const EdgeInsets.all(14),
            child: Row(children: [
              Container(width: 40, height: 40,
                  decoration: BoxDecoration(shape: BoxShape.circle,
                      color: color.withOpacity(0.1)),
                  child: Center(child: Text(s.avatar,
                      style: TextStyle(color: color, fontWeight: FontWeight.w800)))),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(s.name, style: const TextStyle(
                    fontSize: 13, fontWeight: FontWeight.w700)),
                ClipRRect(
                  borderRadius: BorderRadius.circular(3),
                  child: LinearProgressIndicator(
                    value: s.attendance,
                    backgroundColor: color.withOpacity(0.1),
                    valueColor: AlwaysStoppedAnimation<Color>(color),
                    minHeight: 5,
                  ),
                ),
              ])),
              const SizedBox(width: 12),
              Text('${(s.attendance * 100).toInt()}%',
                  style: TextStyle(fontSize: 14,
                      fontWeight: FontWeight.w900, color: color)),
            ]),
          );
        }),
      ]),
    );
  }
}

class _AssignmentTab extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          _MetricCard(label: 'Submitted', value: '83',
              icon: Icons.assignment_turned_in_outlined, color: AppTheme.green, trend: ''),
          const SizedBox(width: 12),
          _MetricCard(label: 'Pending', value: '32',
              icon: Icons.pending_outlined, color: AppTheme.orange, trend: ''),
          const SizedBox(width: 12),
          _MetricCard(label: 'Avg Score', value: '76%',
              icon: Icons.grade_outlined, color: AppTheme.blue, trend: '+3%'),
        ]).animate().fadeIn(),
        const SizedBox(height: 24),

        const SectionHeader(title: '📋 Submission Rate by Assignment'),
        const SizedBox(height: 12),
        ...Provider.of<TeacherProvider>(context).assignmentList.map((a) {
          final pct = a.submitted / a.total;
          return AppCard(
            padding: const EdgeInsets.all(14),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Expanded(child: Text(a.title,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700))),
                Text('${a.submitted}/${a.total}',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800,
                        color: pct == 1.0 ? AppTheme.green : AppTheme.orange)),
              ]),
              const SizedBox(height: 4),
              Text('${a.classSection} • Due ${a.dueDate}',
                  style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: LinearProgressIndicator(
                  value: pct,
                  backgroundColor: AppTheme.primary.withOpacity(0.1),
                  valueColor: AlwaysStoppedAnimation<Color>(
                      pct == 1.0 ? AppTheme.green : AppTheme.primary),
                  minHeight: 8,
                ),
              ),
            ]),
          ).animate().fadeIn(delay: 100.ms);
        }),
      ]),
    );
  }
}

class _MetricCard extends StatelessWidget {
  final String label, value, trend;
  final IconData icon;
  final Color color;
  const _MetricCard({required this.label, required this.value,
    required this.icon, required this.color, required this.trend});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05),
              blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(fontSize: 18,
              fontWeight: FontWeight.w900, color: color)),
          Text(label, style: const TextStyle(fontSize: 9, color: AppTheme.grey)),
          if (trend.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(trend, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700,
                color: trend.startsWith('+') ? AppTheme.green : AppTheme.red)),
          ],
        ]),
      ),
    );
  }
}

class _DistRow extends StatelessWidget {
  final String grade;
  final int count, total;
  final Color color;
  const _DistRow({required this.grade, required this.count,
    required this.total, required this.color});

  @override
  Widget build(BuildContext context) {
    return Row(children: [
      SizedBox(width: 52,
          child: Text(grade, style: TextStyle(fontSize: 12,
              fontWeight: FontWeight.w700, color: color))),
      Expanded(
        child: ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: count / total,
            backgroundColor: color.withOpacity(0.1),
            valueColor: AlwaysStoppedAnimation<Color>(color),
            minHeight: 10,
          ),
        ),
      ),
      const SizedBox(width: 8),
      Text('$count', style: const TextStyle(fontSize: 12,
          fontWeight: FontWeight.w700, color: AppTheme.grey)),
    ]);
  }
}
