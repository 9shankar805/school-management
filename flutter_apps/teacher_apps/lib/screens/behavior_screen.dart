import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class BehaviorScreen extends StatefulWidget {
  const BehaviorScreen({super.key});
  @override
  State<BehaviorScreen> createState() => _BehaviorScreenState();
}

class _BehaviorScreenState extends State<BehaviorScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;
  final Map<String, int> _points = {};
  int _selectedClass = 0;

  final List<Map<String, dynamic>> _positives = [
    {'label': 'Participation', 'icon': Icons.record_voice_over_rounded, 'color': AppTheme.green, 'pts': 1},
    {'label': 'Helping Others', 'icon': Icons.people_rounded, 'color': AppTheme.blue, 'pts': 2},
    {'label': 'Great Work', 'icon': Icons.star_rounded, 'color': AppTheme.orange, 'pts': 3},
    {'label': 'On Time', 'icon': Icons.access_time_rounded, 'color': AppTheme.teal, 'pts': 1},
    {'label': 'Teamwork', 'icon': Icons.groups_rounded, 'color': AppTheme.purple, 'pts': 2},
    {'label': 'Creativity', 'icon': Icons.lightbulb_rounded, 'color': AppTheme.primary, 'pts': 2},
  ];

  final List<Map<String, dynamic>> _negatives = [
    {'label': 'Disruptive', 'icon': Icons.volume_up_rounded, 'color': AppTheme.red, 'pts': -1},
    {'label': 'Late', 'icon': Icons.timer_off_rounded, 'color': AppTheme.orange, 'pts': -1},
    {'label': 'Off Task', 'icon': Icons.do_not_disturb_rounded, 'color': AppTheme.red, 'pts': -2},
    {'label': 'No Homework', 'icon': Icons.assignment_late_rounded, 'color': AppTheme.orange, 'pts': -1},
  ];

  String? _selectedStudent;

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() { _tab.dispose(); super.dispose(); }

  void _award(String student, int pts) {
    setState(() => _points[student] = (_points[student] ?? 0) + pts);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text('${pts > 0 ? '+$pts' : '$pts'} points for ${student.split(' ').first}'),
      backgroundColor: pts > 0 ? AppTheme.green : AppTheme.red,
      duration: const Duration(seconds: 1),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final studentsList = teacherProvider.studentList;
    for (final s in studentsList) {
      _points.putIfAbsent(s.name, () => 0);
    }

    final sorted = studentsList.toList()
      ..sort((a, b) => (_points[b.name] ?? 0).compareTo(_points[a.name] ?? 0));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Behavior Tracker'),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.grey,
          indicatorColor: AppTheme.primary,
          indicatorSize: TabBarIndicatorSize.label,
          tabs: const [Tab(text: 'Award Points'), Tab(text: 'Leaderboard')],
        ),
      ),
      body: TabBarView(
        controller: _tab,
        children: [
          // Award Points tab
          SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              // Student selector
              const SectionHeader(title: 'Select Student'),
              const SizedBox(height: 12),
              SizedBox(
                height: 80,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: studentsList.length,
                  itemBuilder: (_, i) {
                    final s = studentsList[i];
                    final isSelected = _selectedStudent == s.name;
                    final pts = _points[s.name] ?? 0;
                    return GestureDetector(
                      onTap: () => setState(() => _selectedStudent = s.name),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        margin: const EdgeInsets.only(right: 10),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          gradient: isSelected ? const LinearGradient(
                              colors: [AppTheme.primary, AppTheme.secondary]) : null,
                          color: isSelected ? null : Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06),
                              blurRadius: 6, offset: const Offset(0, 2))],
                        ),
                        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                          Text(s.avatar, style: TextStyle(
                              fontSize: 18, fontWeight: FontWeight.w800,
                              color: isSelected ? Colors.white : AppTheme.primary)),
                          Text(s.name.split(' ').first,
                              style: TextStyle(fontSize: 10,
                                  color: isSelected ? Colors.white70 : AppTheme.grey)),
                          Text('${pts > 0 ? '+' : ''}$pts',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800,
                                  color: isSelected ? Colors.white
                                      : pts >= 0 ? AppTheme.green : AppTheme.red)),
                        ]),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 24),
              const SectionHeader(title: '⭐ Positive Behaviors'),
              const SizedBox(height: 12),
              GridView.count(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisCount: 3,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 1.1,
                children: _positives.map((b) => _BehaviorBtn(
                  behavior: b,
                  onTap: _selectedStudent == null ? null : () => _award(_selectedStudent!, b['pts'] as int),
                )).toList(),
              ),
              const SizedBox(height: 20),
              const SectionHeader(title: '⚠️ Needs Improvement'),
              const SizedBox(height: 12),
              GridView.count(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisCount: 2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 2.2,
                children: _negatives.map((b) => _BehaviorBtn(
                  behavior: b,
                  onTap: _selectedStudent == null ? null : () => _award(_selectedStudent!, b['pts'] as int),
                )).toList(),
              ),
              if (_selectedStudent == null)
                Container(
                  margin: const EdgeInsets.only(top: 16),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppTheme.orange.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppTheme.orange.withOpacity(0.3)),
                  ),
                  child: const Row(children: [
                    Icon(Icons.info_outline, color: AppTheme.orange, size: 18),
                    SizedBox(width: 8),
                    Text('Select a student above to award points',
                        style: TextStyle(fontSize: 12, color: AppTheme.orange)),
                  ]),
                ),
            ]),
          ),
          // Leaderboard tab
          ListView.builder(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            itemCount: sorted.length,
            itemBuilder: (_, i) {
              final s = sorted[i];
              final pts = _points[s.name] ?? 0;
              final color = pts > 5 ? AppTheme.green
                  : pts > 0 ? AppTheme.blue
                  : pts < 0 ? AppTheme.red : AppTheme.grey;
              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: i == 0 ? AppTheme.primary.withOpacity(0.08) : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: i == 0 ? Border.all(color: AppTheme.primary.withOpacity(0.2)) : null,
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04),
                      blurRadius: 6, offset: const Offset(0, 2))],
                ),
                child: Row(children: [
                  SizedBox(width: 28,
                    child: Text('#${i + 1}', style: TextStyle(
                        fontSize: 13, fontWeight: FontWeight.w800,
                        color: i == 0 ? AppTheme.primary : AppTheme.grey))),
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: color.withOpacity(0.15)),
                    child: Center(child: Text(s.avatar,
                        style: TextStyle(color: color, fontWeight: FontWeight.w800))),
                  ),
                  const SizedBox(width: 12),
                  Expanded(child: Text(s.name,
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700))),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                        color: color.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(20)),
                    child: Text('${pts > 0 ? '+' : ''}$pts pts',
                        style: TextStyle(fontSize: 13,
                            fontWeight: FontWeight.w800, color: color)),
                  ),
                ]),
              ).animate().fadeIn(delay: (i * 50).ms);
            },
          ),
        ],
      ),
    );
  }
}

class _BehaviorBtn extends StatelessWidget {
  final Map<String, dynamic> behavior;
  final VoidCallback? onTap;
  const _BehaviorBtn({required this.behavior, this.onTap});

  @override
  Widget build(BuildContext context) {
    final color = behavior['color'] as Color;
    final pts = behavior['pts'] as int;
    return GestureDetector(
      onTap: onTap,
      child: Opacity(
        opacity: onTap == null ? 0.4 : 1.0,
        child: Container(
          decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: color.withOpacity(0.2))),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(behavior['icon'] as IconData, color: color, size: 24),
            const SizedBox(height: 4),
            Text(behavior['label'] as String,
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: color)),
            Text('${pts > 0 ? '+' : ''}$pts',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: color)),
          ]),
        ),
      ),
    );
  }
}
