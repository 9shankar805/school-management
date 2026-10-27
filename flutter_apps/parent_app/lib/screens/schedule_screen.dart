import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class ScheduleScreen extends StatefulWidget {
  const ScheduleScreen({super.key});
  @override
  State<ScheduleScreen> createState() => _ScheduleScreenState();
}

class _ScheduleScreenState extends State<ScheduleScreen> with SingleTickerProviderStateMixin {
  late TabController _dayTab;
  final days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

  @override
  void initState() {
    super.initState();
    _dayTab = TabController(length: days.length, vsync: this, initialIndex: 1);
  }

  @override
  void dispose() {
    _dayTab.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Weekly Class Routine'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        bottom: TabBar(
          controller: _dayTab,
          isScrollable: true,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.grey,
          indicatorColor: AppTheme.primary,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: days.map((d) => Tab(text: d)).toList(),
        ),
      ),
      body: TabBarView(
        controller: _dayTab,
        children: days.map((day) {
          return _DayRoutineView(day: day, child: child);
        }).toList(),
      ),
    );
  }
}

class _DayRoutineView extends StatelessWidget {
  final String day;
  final Child child;
  const _DayRoutineView({required this.day, required this.child});

  @override
  Widget build(BuildContext context) {
    final periods = AppData.schedule;

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 40),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '$day Timetable',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
              ),
              Text(
                '${child.grade} - ${child.section}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.primary),
              ),
            ],
          ),
          const SizedBox(height: 14),
          ...periods.asMap().entries.map((entry) {
            final idx = entry.key;
            final item = entry.value;
            final isCurrent = idx == 0;

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              child: AppCard(
                padding: const EdgeInsets.all(16),
                border: isCurrent ? Border.all(color: AppTheme.primary, width: 1.5) : null,
                child: Row(
                  children: [
                    Container(
                      width: 54,
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: isCurrent ? AppTheme.primary : AppTheme.lightBg,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'P${idx + 1}',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                              color: isCurrent ? Colors.white : AppTheme.primary,
                            ),
                          ),
                          Text(
                            item['time'].toString().split(' ').first,
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                              color: isCurrent ? Colors.white70 : AppTheme.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(item['subject'] as String, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                              if (isCurrent) ...[
                                const SizedBox(width: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(color: AppTheme.greenLight, borderRadius: BorderRadius.circular(6)),
                                  child: const Text('ACTIVE NOW', style: TextStyle(fontSize: 8, fontWeight: FontWeight.w900, color: AppTheme.green)),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '${item['teacher']} • ${item['room']}',
                            style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      item['duration'] as String,
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.grey),
                    ),
                  ],
                ),
              ),
            ).animate().fadeIn(delay: (idx * 50).ms);
          }),
        ],
      ),
    );
  }
}
