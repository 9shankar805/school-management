import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class ScheduleScreen extends StatefulWidget {
  const ScheduleScreen({super.key});
  @override
  State<ScheduleScreen> createState() => _ScheduleScreenState();
}

class _ScheduleScreenState extends State<ScheduleScreen> {
  int _selectedDay = 0;

  final List<Map<String, String>> _days = [
    {'label': 'Sun', 'num': '17'}, {'label': 'Mon', 'num': '18'},
    {'label': 'Tue', 'num': '19'}, {'label': 'Wed', 'num': '20'},
    {'label': 'Thu', 'num': '21'}, {'label': 'Fri', 'num': '22'},
    {'label': 'Sat', 'num': '23'},
  ];

  // Weekly schedule per day index
  final List<List<Map<String, dynamic>>> _weekSchedule = [
    [], // Sunday
    [ // Monday
      {'time': '08:30', 'end': '09:30', 'class': 'Mathematics', 'section': 'Grade 10 - A', 'room': 'Room 101', 'colorHex': 0xFFE3F2FD},
      {'time': '10:00', 'end': '11:00', 'class': 'Mathematics', 'section': 'Grade 10 - B', 'room': 'Room 102', 'colorHex': 0xFFE8EAF6},
      {'time': '13:00', 'end': '14:00', 'class': 'Advanced Calculus', 'section': 'Grade 11 - A', 'room': 'Room 305', 'colorHex': 0xFFF3E5F5},
      {'time': '14:30', 'end': '15:30', 'class': 'Statistics', 'section': 'Grade 12 - A', 'room': 'Room 201', 'colorHex': 0xFFE8F5E9},
    ],
    [ // Tuesday
      {'time': '09:00', 'end': '10:00', 'class': 'Mathematics', 'section': 'Grade 10 - A', 'room': 'Room 101', 'colorHex': 0xFFE3F2FD},
      {'time': '11:00', 'end': '12:00', 'class': 'Advanced Calculus', 'section': 'Grade 11 - A', 'room': 'Room 305', 'colorHex': 0xFFF3E5F5},
    ],
    [ // Wednesday
      {'time': '08:30', 'end': '09:30', 'class': 'Mathematics', 'section': 'Grade 10 - B', 'room': 'Room 102', 'colorHex': 0xFFE8EAF6},
      {'time': '10:00', 'end': '11:00', 'class': 'Statistics', 'section': 'Grade 12 - A', 'room': 'Room 201', 'colorHex': 0xFFE8F5E9},
      {'time': '14:00', 'end': '15:00', 'class': 'Mathematics', 'section': 'Grade 10 - A', 'room': 'Room 101', 'colorHex': 0xFFE3F2FD},
    ],
    [ // Thursday
      {'time': '09:00', 'end': '10:00', 'class': 'Advanced Calculus', 'section': 'Grade 11 - A', 'room': 'Room 305', 'colorHex': 0xFFF3E5F5},
      {'time': '13:00', 'end': '14:00', 'class': 'Statistics', 'section': 'Grade 12 - A', 'room': 'Room 201', 'colorHex': 0xFFE8F5E9},
    ],
    [ // Friday
      {'time': '08:30', 'end': '09:30', 'class': 'Mathematics', 'section': 'Grade 10 - A', 'room': 'Room 101', 'colorHex': 0xFFE3F2FD},
      {'time': '10:00', 'end': '11:00', 'class': 'Mathematics', 'section': 'Grade 10 - B', 'room': 'Room 102', 'colorHex': 0xFFE8EAF6},
    ],
    [], // Saturday
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacher = Provider.of<TeacherProvider>(context);

    List<Map<String, dynamic>> todaySlots = _weekSchedule[_selectedDay];
    if (teacher.teacherClasses.isNotEmpty) {
      // In Nepal, Sunday (index 0) to Friday (index 5) are school days, Saturday (index 6) is off
      if (_selectedDay == 6) {
        todaySlots = [];
      } else {
        final colors = [0xFFE3F2FD, 0xFFE8EAF6, 0xFFF3E5F5, 0xFFE8F5E9, 0xFFFFF3E0];
        todaySlots = teacher.teacherClasses.asMap().entries.map((entry) {
          final i = entry.key;
          final c = entry.value;
          final color = colors[i % colors.length];
          return {
            'time': c.time.split(' - ').first,
            'end': c.time.contains(' - ') ? c.time.split(' - ').last : '10:00 AM',
            'class': c.subject.isNotEmpty ? c.subject : 'Lecture',
            'section': c.name,
            'room': 'Room ${c.room}',
            'colorHex': color,
          };
        }).toList();
      }
    }

    final totalStudents = todaySlots.isEmpty
        ? 0
        : (teacher.studentsCount > 0 ? teacher.studentsCount : todaySlots.length * 30);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Schedule'),
        automaticallyImplyLeading: false,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(20)),
            child: const Row(children: [
              Text('Academic Year 2081',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
              SizedBox(width: 4),
              Icon(Icons.keyboard_arrow_down, size: 16),
            ]),
          ),
        ],
      ),
      body: Column(children: [
        // Day selector
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: List.generate(_days.length, (i) {
              final isSelected = i == _selectedDay;
              return GestureDetector(
                onTap: () => setState(() => _selectedDay = i),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 40, height: 58,
                  decoration: BoxDecoration(
                    gradient: isSelected ? const LinearGradient(
                        colors: [AppTheme.primary, AppTheme.secondary],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter) : null,
                    color: isSelected ? null
                        : (isDark ? AppTheme.darkCard : Colors.white),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                    Text(_days[i]['label']!,
                        style: TextStyle(fontSize: 10,
                            color: isSelected ? Colors.white : AppTheme.grey)),
                    const SizedBox(height: 4),
                    Text(_days[i]['num']!,
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700,
                            color: isSelected ? Colors.white : null)),
                  ]),
                ),
              );
            }),
          ),
        ),
        const SizedBox(height: 16),
        // Stats for selected day
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Row(children: [
            _DayStat(label: 'Classes', value: '${todaySlots.length}',
                color: AppTheme.primary),
            const SizedBox(width: 12),
            _DayStat(label: 'Students', value: '$totalStudents',
                color: AppTheme.blue),
            const SizedBox(width: 12),
            _DayStat(label: 'Hours', value: '${todaySlots.length * 1}h',
                color: AppTheme.green),
          ]),
        ),
        const SizedBox(height: 16),
        Expanded(
          child: todaySlots.isEmpty
              ? Center(
                  child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                    Icon(Icons.event_available_rounded,
                        size: 64, color: AppTheme.grey.withOpacity(0.4)),
                    const SizedBox(height: 12),
                    const Text('No classes today',
                        style: TextStyle(color: AppTheme.grey, fontSize: 16)),
                    const SizedBox(height: 4),
                    const Text('Enjoy your free day!',
                        style: TextStyle(color: AppTheme.grey, fontSize: 13)),
                  ]),
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                  itemCount: todaySlots.length,
                  itemBuilder: (_, i) {
                    final slot = todaySlots[i];
                    return _ScheduleCard(slot: slot)
                        .animate().fadeIn(delay: (i * 80).ms).slideX(begin: 0.1);
                  },
                ),
        ),
      ]),
    );
  }
}

class _DayStat extends StatelessWidget {
  final String label, value;
  final Color color;
  const _DayStat({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(children: [
          Text(value, style: TextStyle(
              fontSize: 18, fontWeight: FontWeight.w900, color: color)),
          Text(label, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
        ]),
      ),
    );
  }
}

class _ScheduleCard extends StatelessWidget {
  final Map<String, dynamic> slot;
  const _ScheduleCard({required this.slot});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        SizedBox(
          width: 52,
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(slot['time'] as String,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
            Text(slot['end'] as String,
                style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
          ]),
        ),
        const SizedBox(width: 8),
        Container(
          width: 3, height: 72,
          decoration: BoxDecoration(
              color: AppTheme.primary.withOpacity(0.3),
              borderRadius: BorderRadius.circular(2)),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Color(slot['colorHex'] as int),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(slot['class'] as String,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700,
                      color: AppTheme.dark)),
              const SizedBox(height: 4),
              Row(children: [
                const Icon(Icons.group_outlined, size: 12, color: AppTheme.grey),
                const SizedBox(width: 4),
                Text(slot['section'] as String,
                    style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                const SizedBox(width: 12),
                const Icon(Icons.room_outlined, size: 12, color: AppTheme.grey),
                const SizedBox(width: 4),
                Text(slot['room'] as String,
                    style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
              ]),
            ]),
          ),
        ),
      ]),
    );
  }
}
