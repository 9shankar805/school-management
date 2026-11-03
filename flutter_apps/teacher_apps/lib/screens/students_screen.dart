import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../services/api_service.dart';

class StudentsScreen extends StatefulWidget {
  final String? filterClass;
  const StudentsScreen({super.key, this.filterClass});
  @override
  State<StudentsScreen> createState() => _StudentsScreenState();
}

class _StudentsScreenState extends State<StudentsScreen> {
  final _searchCtrl = TextEditingController();
  String _query = '';
  String _selectedClass = 'All';

  List<String> _getClassOptions(TeacherProvider teacher) {
    if (teacher.teacherClasses.isNotEmpty) {
      final opts = <String>['All'];
      for (final c in teacher.teacherClasses) {
        if (!opts.contains(c.section)) opts.add(c.section);
      }
      return opts;
    }
    return ['All', ...AppData.classes.map((c) => c.section)];
  }

  List<Student> _getFiltered(TeacherProvider teacher) {
    List<Student> list = teacher.studentList;

    if (_selectedClass != 'All') {
      list = list.where((s) => s.grade.toLowerCase().contains(_selectedClass.toLowerCase()) ||
          _selectedClass.toLowerCase().contains(s.grade.toLowerCase())).toList();
    }

    if (_query.isNotEmpty) {
      list = list.where((s) =>
          s.name.toLowerCase().contains(_query.toLowerCase()) ||
          s.rollNo.contains(_query)).toList();
    }
    return list;
  }

  Color _gradeColor(String g) {
    if (g.startsWith('A')) return AppTheme.green;
    if (g.startsWith('B')) return AppTheme.blue;
    if (g.startsWith('C')) return AppTheme.orange;
    return AppTheme.red;
  }

  @override
  void initState() {
    super.initState();
    if (widget.filterClass != null) _selectedClass = widget.filterClass!;
  }

  @override
  Widget build(BuildContext context) {
    final teacher = Provider.of<TeacherProvider>(context);
    final classOptions = _getClassOptions(teacher);
    final filtered = _getFiltered(teacher);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Students'),
        leading: CircleIconButton(
            icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
      ),
      body: Column(children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
          child: TextField(
            controller: _searchCtrl,
            onChanged: (v) => setState(() => _query = v),
            decoration: InputDecoration(
              hintText: 'Search students...',
              prefixIcon: const Icon(Icons.search_rounded, color: AppTheme.grey),
              filled: true,
              fillColor: isDark ? AppTheme.darkCard : Colors.white,
              border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none),
              contentPadding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 36,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: classOptions.length,
            itemBuilder: (_, i) {
              final opt = classOptions[i];
              final sel = opt == _selectedClass;
              return GestureDetector(
                onTap: () => setState(() => _selectedClass = opt),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    gradient: sel ? const LinearGradient(
                        colors: [AppTheme.primary, AppTheme.secondary]) : null,
                    color: sel ? null : (isDark ? AppTheme.darkCard : Colors.white),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(opt, style: TextStyle(
                      fontSize: 12, fontWeight: FontWeight.w600,
                      color: sel ? Colors.white : AppTheme.grey)),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 12),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
            itemCount: filtered.length,
            itemBuilder: (_, i) {
              final s = filtered[i];
              return AppCard(
                padding: const EdgeInsets.all(14),
                onTap: () => Navigator.push(context, MaterialPageRoute(
                    builder: (_) => StudentDetailScreen(student: s))),
                child: Row(children: [
                  Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(colors: [
                        _gradeColor(s.grade),
                        _gradeColor(s.grade).withOpacity(0.6),
                      ]),
                    ),
                    child: Center(child: Text(s.avatar,
                        style: const TextStyle(color: Colors.white,
                            fontWeight: FontWeight.w800, fontSize: 18))),
                  ),
                  const SizedBox(width: 12),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(s.name, style: const TextStyle(
                        fontSize: 14, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 4),
                    Row(children: [
                      const Icon(Icons.school_outlined, size: 12, color: AppTheme.grey),
                      const SizedBox(width: 4),
                      Text('GPA: ${s.gpa.toStringAsFixed(2)}',
                          style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                      const SizedBox(width: 12),
                      const Icon(Icons.how_to_reg_outlined, size: 12, color: AppTheme.grey),
                      const SizedBox(width: 4),
                      Text('${(s.attendance * 100).toInt()}%',
                          style: TextStyle(fontSize: 12,
                              color: s.attendance >= 0.85 ? AppTheme.green
                                  : s.attendance >= 0.75 ? AppTheme.orange : AppTheme.red)),
                    ]),
                  ])),
                  Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                    GradeBadge(grade: s.grade),
                    const SizedBox(height: 4),
                    Text('#${s.rank}', style: const TextStyle(
                        fontSize: 11, color: AppTheme.grey)),
                  ]),
                ]),
              ).animate().fadeIn(delay: (i * 50).ms);
            },
          ),
        ),
      ]),
    );
  }
}

class StudentDetailScreen extends StatelessWidget {
  final Student student;
  const StudentDetailScreen({super.key, required this.student});

  Color _gradeColor(String g) {
    if (g.startsWith('A')) return AppTheme.green;
    if (g.startsWith('B')) return AppTheme.blue;
    if (g.startsWith('C')) return AppTheme.orange;
    return AppTheme.red;
  }

  void _showEditStudentModal(BuildContext context, Student student) {
    final nameCtrl = TextEditingController(text: student.name);
    final rollCtrl = TextEditingController(text: student.rollNo);
    final parentCtrl = TextEditingController(text: student.parentName);
    final phoneCtrl = TextEditingController(text: student.parentPhone);
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;

          return Container(
            padding: EdgeInsets.fromLTRB(
              20,
              16,
              20,
              MediaQuery.of(ctx).viewInsets.bottom + 24,
            ),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.15),
                  blurRadius: 16,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.grey.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.manage_accounts_rounded, color: AppTheme.primary, size: 22),
                      ),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Edit Student Details (विद्यार्थी सम्पादन)',
                              style: TextStyle(fontSize: 15.5, fontWeight: FontWeight.w900),
                            ),
                            Text(
                              'Class In-Charge Authority',
                              style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded),
                        onPressed: () => Navigator.pop(sheetCtx),
                      ),
                    ],
                  ),
                  const Divider(height: 20),

                  // Roll No & Name
                  Row(
                    children: [
                      SizedBox(
                        width: 90,
                        child: TextField(
                          controller: rollCtrl,
                          keyboardType: TextInputType.number,
                          decoration: InputDecoration(
                            labelText: 'Roll No',
                            labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                            prefixIcon: const Icon(Icons.numbers_rounded, size: 16),
                            filled: true,
                            fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: nameCtrl,
                          decoration: InputDecoration(
                            labelText: 'Student Full Name',
                            labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                            prefixIcon: const Icon(Icons.person_rounded, size: 16),
                            filled: true,
                            fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Parent / Guardian Name
                  TextField(
                    controller: parentCtrl,
                    decoration: InputDecoration(
                      labelText: 'Parent / Guardian Name',
                      labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                      prefixIcon: const Icon(Icons.escalator_warning_rounded, size: 16),
                      filled: true,
                      fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Parent Phone Number
                  TextField(
                    controller: phoneCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      labelText: 'Parent Mobile Phone',
                      labelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                      prefixIcon: const Icon(Icons.phone_rounded, size: 16),
                      filled: true,
                      fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 18),

                  // Save Button
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      onPressed: isSaving
                          ? null
                          : () async {
                              final newName = nameCtrl.text.trim();
                              final newRoll = rollCtrl.text.trim();
                              final newParent = parentCtrl.text.trim();
                              final newPhone = phoneCtrl.text.trim();

                              if (newName.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Please enter student name')),
                                );
                                return;
                              }

                              setModalState(() => isSaving = true);

                              try {
                                if (student.id != null) {
                                  await ApiService().updateStudent(
                                    student.id.toString(),
                                    {
                                      'name': newName,
                                      'roll_no': newRoll,
                                      'parent_name': newParent,
                                      'parent_phone': newPhone,
                                    },
                                  );
                                }

                                if (context.mounted) {
                                  final teacher = Provider.of<TeacherProvider>(context, listen: false);
                                  teacher.fetchStudents();
                                  Navigator.pop(sheetCtx);
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      backgroundColor: AppTheme.green,
                                      content: Row(
                                        children: [
                                          const Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
                                          const SizedBox(width: 8),
                                          Text('Details for "$newName" updated successfully!'),
                                        ],
                                      ),
                                    ),
                                  );
                                }
                              } catch (e) {
                                if (context.mounted) {
                                  setModalState(() => isSaving = false);
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(content: Text('Error saving changes: $e')),
                                  );
                                }
                              }
                            },
                      icon: isSaving
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.save_rounded, size: 18),
                      label: Text(
                        isSaving ? 'Saving Changes...' : 'Save Student Details',
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final color = _gradeColor(student.grade);
    return Scaffold(
      appBar: AppBar(
        title: Text(student.name),
        leading: CircleIconButton(
            icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.edit_note_rounded, size: 24, color: AppTheme.primary),
            tooltip: 'Edit Student Details',
            onPressed: () => _showEditStudentModal(context, student),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        child: Column(children: [
          GradientCard(
            colors: [color, color.withOpacity(0.6)],
            child: Row(children: [
              Container(
                width: 72, height: 72,
                decoration: BoxDecoration(
                    color: Colors.white24, shape: BoxShape.circle),
                child: Center(child: Text(student.avatar,
                    style: const TextStyle(color: Colors.white,
                        fontSize: 28, fontWeight: FontWeight.w900))),
              ),
              const SizedBox(width: 16),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(student.name, style: const TextStyle(
                    color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                const SizedBox(height: 4),
                Text('Rank #${student.rank}',
                    style: const TextStyle(color: Colors.white70, fontSize: 13)),
                const SizedBox(height: 12),
                Row(children: [
                  _InfoChip(label: 'GPA', value: student.gpa.toStringAsFixed(2)),
                  const SizedBox(width: 8),
                  _InfoChip(label: 'Attendance',
                      value: '${(student.attendance * 100).toInt()}%'),
                  const SizedBox(width: 8),
                  _InfoChip(label: 'Grade', value: student.grade),
                ]),
              ])),
            ]),
          ),
          const SizedBox(height: 20),
          const SectionHeader(title: 'Performance Overview'),
          const SizedBox(height: 12),
          AppCard(child: Column(children: [
            _ProgressRow(label: 'Mathematics', value: 0.88, color: AppTheme.blue),
            const SizedBox(height: 12),
            _ProgressRow(label: 'Advanced Calculus', value: 0.75, color: AppTheme.purple),
            const SizedBox(height: 12),
            _ProgressRow(label: 'Statistics', value: 0.92, color: AppTheme.green),
          ])),
          const SizedBox(height: 20),
          const SectionHeader(title: 'Recent Assignments'),
          const SizedBox(height: 12),
          ...Provider.of<TeacherProvider>(context).assignmentList.take(3).map((a) => AppCard(
            padding: const EdgeInsets.all(14),
            child: Row(children: [
              Container(
                width: 40, height: 40,
                decoration: BoxDecoration(
                    color: Color(a.colorHex),
                    borderRadius: BorderRadius.circular(12)),
                child: const Icon(Icons.assignment_outlined,
                    size: 20, color: AppTheme.dark),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(a.title, style: const TextStyle(
                    fontSize: 13, fontWeight: FontWeight.w700)),
                Text(a.dueDate, style: const TextStyle(
                    fontSize: 11, color: AppTheme.grey)),
              ])),
              PillBadge(label: 'Submitted',
                  color: AppTheme.green.withOpacity(0.1),
                  textColor: AppTheme.green),
            ]),
          )),
        ]),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  final String label, value;
  const _InfoChip({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
          color: Colors.white24, borderRadius: BorderRadius.circular(20)),
      child: Column(children: [
        Text(value, style: const TextStyle(
            color: Colors.white, fontSize: 13, fontWeight: FontWeight.w800)),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 9)),
      ]),
    );
  }
}

class _ProgressRow extends StatelessWidget {
  final String label;
  final double value;
  final Color color;
  const _ProgressRow({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
        Text('${(value * 100).toInt()}%',
            style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w700)),
      ]),
      const SizedBox(height: 6),
      ClipRRect(
        borderRadius: BorderRadius.circular(4),
        child: LinearProgressIndicator(
          value: value,
          backgroundColor: color.withOpacity(0.1),
          valueColor: AlwaysStoppedAnimation<Color>(color),
          minHeight: 8,
        ),
      ),
    ]);
  }
}
