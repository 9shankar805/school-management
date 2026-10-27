import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class GradebookScreen extends StatefulWidget {
  const GradebookScreen({super.key});
  @override
  State<GradebookScreen> createState() => _GradebookScreenState();
}

class _GradebookScreenState extends State<GradebookScreen> {
  int _selectedClass = 0;

  Color _gradeColor(String g) {
    if (g.startsWith('A')) return AppTheme.green;
    if (g.startsWith('B')) return AppTheme.blue;
    if (g.startsWith('C')) return AppTheme.orange;
    return AppTheme.red;
  }

  double _calcClassAvg(List<Student> students) {
    if (students.isEmpty) return 3.6;
    const gradePoints = {
      'A+': 4.0, 'A': 4.0, 'A-': 3.7,
      'B+': 3.3, 'B': 3.0, 'B-': 2.7,
      'C+': 2.3, 'C': 2.0,
    };
    double total = 0;
    for (final s in students) {
      total += gradePoints[s.grade] ?? s.gpa;
    }
    return total / students.length;
  }

  @override
  Widget build(BuildContext context) {
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final classesList = teacherProvider.teacherClasses;
    if (_selectedClass >= classesList.length && classesList.isNotEmpty) {
      _selectedClass = 0;
    }
    final cls = classesList.isNotEmpty ? classesList[_selectedClass] : AppData.classes.first;

    final allStudents = teacherProvider.studentList;
    final classStudents = allStudents.where((s) {
      if (cls.classId != null && s.classId != null && s.classId != cls.classId) return false;
      if (cls.sectionId != null && s.sectionId != null && s.sectionId != cls.sectionId) return false;
      return true;
    }).toList();
    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;
    final classAvg = _calcClassAvg(activeStudents);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Gradebook'),
        leading: CircleIconButton(
            icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(icon: Icons.download_outlined, onTap: () {}, size: 38),
          const SizedBox(width: 16),
        ],
      ),
      body: Column(children: [
        // Class selector
        SizedBox(
          height: 44,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: classesList.length,
            itemBuilder: (_, i) {
              final sel = i == _selectedClass;
              return GestureDetector(
                onTap: () => setState(() => _selectedClass = i),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    gradient: sel ? const LinearGradient(
                        colors: [AppTheme.primary, AppTheme.secondary]) : null,
                    color: sel ? null : (Theme.of(context).brightness == Brightness.dark
                        ? AppTheme.darkCard : Colors.white),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(classesList[i].section,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600,
                          color: sel ? Colors.white : AppTheme.grey)),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 12),
        // Summary card
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: GradientCard(
            colors: const [AppTheme.primary, AppTheme.secondary],
            padding: const EdgeInsets.all(16),
            child: Row(children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(cls.name, style: const TextStyle(
                    color: Colors.white, fontSize: 16, fontWeight: FontWeight.w800)),
                Text(cls.section, style: const TextStyle(
                    color: Colors.white70, fontSize: 12)),
              ])),
              _GradeStatBox(label: 'Students', value: '${activeStudents.length}'),
              const SizedBox(width: 12),
              _GradeStatBox(label: 'Class Avg', value: classAvg.toStringAsFixed(1)),
              const SizedBox(width: 12),
              _GradeStatBox(label: 'Top Grade', value: 'A+'),
            ]),
          ),
        ),
        const SizedBox(height: 16),
        // Grade distribution
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: AppCard(
            padding: const EdgeInsets.all(14),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('Grade Distribution',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              Row(children: [
                _GradeBar(grade: 'A+', count: 2, total: 12, color: AppTheme.green),
                const SizedBox(width: 8),
                _GradeBar(grade: 'A', count: 3, total: 12, color: AppTheme.teal),
                const SizedBox(width: 8),
                _GradeBar(grade: 'B+', count: 4, total: 12, color: AppTheme.blue),
                const SizedBox(width: 8),
                _GradeBar(grade: 'B', count: 2, total: 12, color: AppTheme.orange),
                const SizedBox(width: 8),
                _GradeBar(grade: 'C', count: 1, total: 12, color: AppTheme.red),
              ]),
            ]),
          ),
        ),
        const SizedBox(height: 16),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: SectionHeader(
            title: 'Student Grades',
            actionLabel: 'Export',
            onAction: () {},
          ),
        ),
        const SizedBox(height: 8),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
            itemCount: activeStudents.length,
            itemBuilder: (_, i) {
              final s = activeStudents[i];
              return AppCard(
                padding: const EdgeInsets.all(14),
                onTap: () {},
                child: Row(children: [
                  Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: _gradeColor(s.grade).withOpacity(0.15),
                    ),
                    child: Center(child: Text(s.avatar,
                        style: TextStyle(color: _gradeColor(s.grade),
                            fontWeight: FontWeight.w800, fontSize: 16))),
                  ),
                  const SizedBox(width: 12),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(s.name, style: const TextStyle(
                        fontSize: 13, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 4),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(3),
                      child: LinearProgressIndicator(
                        value: s.gpa / 4.0,
                        backgroundColor: _gradeColor(s.grade).withOpacity(0.1),
                        valueColor: AlwaysStoppedAnimation<Color>(_gradeColor(s.grade)),
                        minHeight: 5,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text('GPA: ${s.gpa.toStringAsFixed(2)}',
                        style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                  ])),
                  const SizedBox(width: 12),
                  GradeBadge(grade: s.grade),
                ]),
              ).animate().fadeIn(delay: (i * 40).ms);
            },
          ),
        ),
      ]),
    );
  }
}

class _GradeStatBox extends StatelessWidget {
  final String label, value;
  const _GradeStatBox({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      Text(value, style: const TextStyle(
          color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900)),
      Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10)),
    ]);
  }
}

class _GradeBar extends StatelessWidget {
  final String grade;
  final int count, total;
  final Color color;
  const _GradeBar(
      {required this.grade, required this.count,
      required this.total, required this.color});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(children: [
        Container(
          height: 60,
          decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8)),
          child: Align(
            alignment: Alignment.bottomCenter,
            child: FractionallySizedBox(
              heightFactor: count / total,
              child: Container(
                decoration: BoxDecoration(
                    color: color, borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ),
        ),
        const SizedBox(height: 4),
        Text(grade, style: TextStyle(
            fontSize: 10, fontWeight: FontWeight.w700, color: color)),
        Text('$count', style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
      ]),
    );
  }
}
