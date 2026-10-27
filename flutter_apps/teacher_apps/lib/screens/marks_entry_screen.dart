import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../services/api_service.dart';
import '../providers/teacher_provider.dart';

class MarksEntryScreen extends StatefulWidget {
  final String? initialExam;
  final String? initialClass;
  final String? initialSubject;

  const MarksEntryScreen({
    super.key,
    this.initialExam,
    this.initialClass,
    this.initialSubject,
  });

  @override
  State<MarksEntryScreen> createState() => _MarksEntryScreenState();
}

class _MarksEntryScreenState extends State<MarksEntryScreen> {
  final ApiService _apiService = ApiService();
  bool _saving = false;

  String _selectedExam = 'First Terminal Examination 2081';
  String? _selectedClass;
  String? _selectedSubject;
  String? _lastClassKey;

  List<Map<String, dynamic>> _studentsMarks = [];

  @override
  void initState() {
    super.initState();
    if (widget.initialExam != null) _selectedExam = widget.initialExam!;
    if (widget.initialClass != null) _selectedClass = widget.initialClass!;
    if (widget.initialSubject != null) _selectedSubject = widget.initialSubject!;
  }

  void _populateStudents(List<Student> students) {
    _studentsMarks = students.asMap().entries.map((e) {
      final s = e.value;
      final th = 54 + (e.key * 3 % 19);
      final pr = 21 + (e.key % 4);
      final total = th + pr;
      return {
        'student_id': s.id ?? (e.key + 1),
        'roll_no': s.rollNo,
        'name': s.name,
        'avatar': s.avatar,
        'theory': TextEditingController(text: '$th'),
        'practical': TextEditingController(text: '$pr'),
        'total': total,
        'grade': _calculateCdcGrade(total),
        'gpa': _calculateCdcGpa(total),
      };
    }).toList();
  }

  String _calculateCdcGrade(num total) {
    if (total >= 90) return 'A+';
    if (total >= 80) return 'A';
    if (total >= 70) return 'B+';
    if (total >= 60) return 'B';
    if (total >= 50) return 'C+';
    if (total >= 40) return 'C';
    if (total >= 35) return 'D';
    return 'NG';
  }

  String _calculateCdcGpa(num total) {
    if (total >= 90) return '4.0';
    if (total >= 80) return '3.6';
    if (total >= 70) return '3.2';
    if (total >= 60) return '2.8';
    if (total >= 50) return '2.4';
    if (total >= 40) return '2.0';
    if (total >= 35) return '1.6';
    return '0.0';
  }

  Color _getGradeColor(String grade) {
    if (grade == 'A+') return const Color(0xFF10B981);
    if (grade == 'A') return const Color(0xFF059669);
    if (grade == 'B+') return const Color(0xFF2563EB);
    if (grade == 'B') return const Color(0xFF0284C7);
    if (grade == 'C+') return const Color(0xFFF59E0B);
    if (grade == 'C') return const Color(0xFFD97706);
    if (grade == 'D') return const Color(0xFF78716C);
    return const Color(0xFFEF4444);
  }

  void _recalculate(int index) {
    final item = _studentsMarks[index];
    final th = double.tryParse((item['theory'] as TextEditingController).text) ?? 0;
    final pr = double.tryParse((item['practical'] as TextEditingController).text) ?? 0;
    final total = (th + pr).clamp(0, 100);
    setState(() {
      item['total'] = total;
      item['grade'] = _calculateCdcGrade(total);
      item['gpa'] = _calculateCdcGpa(total);
    });
  }

  void _autoFillFullPracticals() {
    setState(() {
      for (var s in _studentsMarks) {
        (s['practical'] as TextEditingController).text = '25';
        final th = double.tryParse((s['theory'] as TextEditingController).text) ?? 0;
        final total = (th + 25).clamp(0, 100);
        s['total'] = total;
        s['grade'] = _calculateCdcGrade(total);
        s['gpa'] = _calculateCdcGpa(total);
      }
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Auto-filled 25/25 for all internal practical marks'),
        duration: Duration(seconds: 1),
      ),
    );
  }

  void _saveMarks() async {
    setState(() => _saving = true);

    final payload = {
      'exam_name': _selectedExam,
      'class_section': _selectedClass,
      'subject': _selectedSubject,
      'marks': _studentsMarks.map((m) {
        return {
          'student_id': m['student_id'],
          'theory': double.tryParse((m['theory'] as TextEditingController).text) ?? 0,
          'practical': double.tryParse((m['practical'] as TextEditingController).text) ?? 0,
          'score': m['total'],
          'grade': m['grade'],
        };
      }).toList(),
    };

    final res = await _apiService.submitExamMarks('1', payload);

    if (!mounted) return;
    setState(() => _saving = false);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: AppTheme.green, size: 24),
            SizedBox(width: 10),
            Text('Marks Published! 🎯', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
          ],
        ),
        content: Text(
          'Marks for $_selectedSubject ($_selectedClass) have been synchronized with Nepal School Hub MIS and Gradebook Ledger.',
        ),
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pop(context);
            },
            child: const Text('Return to Academic Hub'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final classesList = teacherProvider.teacherClasses;
    final subjectsList = classesList.map((c) => c.subject).toSet().toList();
    if (subjectsList.isEmpty) subjectsList.add('Compulsory Mathematics');

    final classSections = classesList.map((c) => c.section).toList();
    if (classSections.isEmpty) classSections.add('Grade 10 - A');

    if (_selectedClass == null || !classSections.contains(_selectedClass)) {
      _selectedClass = classSections.first;
    }
    if (_selectedSubject == null || !subjectsList.contains(_selectedSubject)) {
      _selectedSubject = subjectsList.first;
    }

    final allStudents = teacherProvider.studentList;
    final classObj = classesList.firstWhere(
      (c) => c.section == _selectedClass,
      orElse: () => classesList.isNotEmpty ? classesList.first : AppData.classes.first,
    );

    final classStudents = allStudents.where((s) {
      if (classObj.classId != null && s.classId != null && s.classId != classObj.classId) return false;
      if (classObj.sectionId != null && s.sectionId != null && s.sectionId != classObj.sectionId) return false;
      return true;
    }).toList();

    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;

    final currentKey = '${classObj.classId ?? classObj.id}_${activeStudents.length}';
    if (_lastClassKey != currentKey || _studentsMarks.isEmpty) {
      _lastClassKey = currentKey;
      _populateStudents(activeStudents);
    }

    final avgScore = _studentsMarks.isEmpty
        ? 0
        : (_studentsMarks.map((e) => (e['total'] as num)).reduce((a, b) => a + b) /
                _studentsMarks.length)
            .toStringAsFixed(1);

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Marks Ledger (प्राप्ताङ्क प्रविष्टि)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
            ),
            Text(
              'CDC Nepal 2078 Grading Standard',
              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.darkSubtext),
            ),
          ],
        ),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: _saving ? null : _saveMarks,
              icon: _saving
                  ? const SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Icon(Icons.cloud_upload_rounded, size: 16),
              label: Text(
                _saving ? 'Saving...' : 'Publish',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Selectors Card
          Container(
            margin: const EdgeInsets.fromLTRB(16, 4, 16, 8),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(
                color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                width: 1,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Column(
              children: [
                _buildSelector(
                  'Exam / Terminal Assessment',
                  _selectedExam,
                  [
                    'First Terminal Examination 2081',
                    'Second Terminal Examination 2081',
                    'Pre-Board / Sent-Up Exam',
                    'Continuous Assessment (CAS)',
                  ],
                  (v) => setState(() => _selectedExam = v!),
                  isDark,
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: _buildSelector(
                        'Class & Section',
                        _selectedClass!,
                        classSections,
                        (v) => setState(() => _selectedClass = v!),
                        isDark,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _buildSelector(
                        'Subject',
                        _selectedSubject!,
                        subjectsList,
                        (v) => setState(() => _selectedSubject = v!),
                        isDark,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.analytics_rounded, size: 14, color: AppTheme.primary),
                        const SizedBox(width: 6),
                        Text(
                          'Class Average: $avgScore%',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: AppTheme.primary,
                          ),
                        ),
                      ],
                    ),
                    GestureDetector(
                      onTap: _autoFillFullPracticals,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppTheme.green.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppTheme.green.withOpacity(0.3)),
                        ),
                        child: const Row(
                          children: [
                            Icon(Icons.bolt_rounded, size: 13, color: AppTheme.green),
                            SizedBox(width: 4),
                            Text(
                              'Autofill PR 25/25',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.green,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Scale Indicator
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 16),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.primary.withOpacity(0.15) : const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppTheme.primary.withOpacity(0.25)),
            ),
            child: const Row(
              children: [
                Icon(Icons.info_outline_rounded, size: 14, color: AppTheme.primary),
                SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'CDC Nepal Scheme: Theory TH (75) + Practical PR (25) = Total 100',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.primary),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Students Mark Entry List
          Expanded(
            child: ListView.builder(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 90),
              itemCount: _studentsMarks.length,
              itemBuilder: (ctx, i) {
                final item = _studentsMarks[i];
                final grade = item['grade'] as String;
                final gpa = item['gpa'] as String;
                final gradeColor = _getGradeColor(grade);

                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkCard : Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                      width: 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.02),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      // Roll No & Avatar
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: gradeColor.withOpacity(0.15),
                          shape: BoxShape.circle,
                        ),
                        child: Center(
                          child: Text(
                            '${item['roll_no']}',
                            style: TextStyle(
                              color: gradeColor,
                              fontWeight: FontWeight.w900,
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),

                      // Student Info
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              item['name'] as String,
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Roll #${item['roll_no']} • GPA $gpa',
                              style: const TextStyle(color: AppTheme.darkSubtext, fontSize: 11),
                            ),
                          ],
                        ),
                      ),

                      // Theory Mark Input
                      SizedBox(
                        width: 58,
                        child: Column(
                          children: [
                            const Text('TH (75)', style: TextStyle(fontSize: 9.5, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700)),
                            const SizedBox(height: 2),
                            TextField(
                              controller: item['theory'] as TextEditingController,
                              keyboardType: TextInputType.number,
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                              decoration: InputDecoration(
                                isDense: true,
                                contentPadding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                                filled: true,
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: BorderSide.none,
                                ),
                              ),
                              onChanged: (_) => _recalculate(i),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Practical Mark Input
                      SizedBox(
                        width: 58,
                        child: Column(
                          children: [
                            const Text('PR (25)', style: TextStyle(fontSize: 9.5, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700)),
                            const SizedBox(height: 2),
                            TextField(
                              controller: item['practical'] as TextEditingController,
                              keyboardType: TextInputType.number,
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                              decoration: InputDecoration(
                                isDense: true,
                                contentPadding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                                filled: true,
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: BorderSide.none,
                                ),
                              ),
                              onChanged: (_) => _recalculate(i),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 10),

                      // Total & Grade Badge
                      Container(
                        width: 48,
                        padding: const EdgeInsets.symmetric(vertical: 6),
                        decoration: BoxDecoration(
                          color: gradeColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: gradeColor.withOpacity(0.3)),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              grade,
                              style: TextStyle(
                                fontWeight: FontWeight.w900,
                                fontSize: 13,
                                color: gradeColor,
                              ),
                            ),
                            Text(
                              '${item['total']}',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: gradeColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ).animate().fadeIn(delay: (i * 25).ms);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSelector(
      String label, String value, List<String> items, ValueChanged<String?> onChanged, bool isDark) {
    return DropdownButtonFormField<String>(
      value: items.contains(value) ? value : items.first,
      isExpanded: true,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
        filled: true,
        fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide.none,
        ),
      ),
      items: items
          .map((item) => DropdownMenuItem(
                value: item,
                child: Text(
                  item,
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
              ))
          .toList(),
      onChanged: onChanged,
    );
  }
}
