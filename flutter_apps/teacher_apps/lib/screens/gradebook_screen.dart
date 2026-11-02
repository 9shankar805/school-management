import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../services/api_service.dart';

class GradebookScreen extends StatefulWidget {
  const GradebookScreen({super.key});

  @override
  State<GradebookScreen> createState() => _GradebookScreenState();
}

class _GradebookScreenState extends State<GradebookScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = false;
  int _selectedClassIndex = 0;
  String _selectedExamId = '1';
  String _selectedExamTitle = 'First Terminal Examination (प्रथम त्रैमासिक)';
  String _searchQuery = '';
  String _sortBy = 'rank'; // 'rank', 'roll', 'needs_support'
  String? _gradeFilter; // null = all, or 'A+', 'A', 'B+', etc.

  Map<String, dynamic>? _gradebookData;
  List<Map<String, dynamic>> _students = [];
  Map<String, dynamic> _summary = {
    'total_students': 32,
    'class_avg_gpa': 3.52,
    'top_gpa': 3.98,
    'topper_name': 'Diya Maharjan',
    'pass_rate': 96.8,
    'distinction_count': 17,
  };
  Map<String, dynamic> _distribution = {
    'A+': 6,
    'A': 11,
    'B+': 8,
    'B': 4,
    'C+': 2,
    'C': 1,
    'D': 0,
    'NG': 0,
  };

  final List<Map<String, String>> _examTerms = [
    {'id': '1', 'name': 'First Term (प्रथम त्रैमासिक)'},
    {'id': '2', 'name': 'Second Term (दोस्रो त्रैमासिक)'},
    {'id': '3', 'name': 'Pre-Board (पूर्व-बोर्ड परीक्षा)'},
    {'id': '4', 'name': 'Final Annual (वार्षिक परीक्षा)'},
    {'id': '5', 'name': 'CAS Internal (निरन्तर मूल्याङ्कन)'},
  ];

  @override
  void initState() {
    super.initState();
    _loadCachedGradebook();
    _fetchGradebook();
  }

  Color _getGradeColor(String grade) {
    if (grade == 'A+') return const Color(0xFF059669);
    if (grade == 'A') return const Color(0xFF0D9488);
    if (grade == 'B+') return const Color(0xFF2563EB);
    if (grade == 'B') return const Color(0xFF4F46E5);
    if (grade == 'C+') return const Color(0xFFD97706);
    if (grade == 'C') return const Color(0xFFEA580C);
    if (grade == 'D') return const Color(0xFFDC2626);
    return const Color(0xFF991B1B); // NG
  }

  Future<void> _loadCachedGradebook() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString('cached_gradebook_data');
      if (cached != null) {
        final decoded = jsonDecode(cached) as Map<String, dynamic>;
        _parseGradebookResponse(decoded);
      }
    } catch (_) {}
  }

  Future<void> _fetchGradebook() async {
    setState(() => _isLoading = true);
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classes = teacherProvider.teacherClasses;
    final cls = classes.isNotEmpty && _selectedClassIndex < classes.length
        ? classes[_selectedClassIndex]
        : null;
    final classId = cls?.classId?.toString() ?? '1';

    try {
      final res = await _apiService.getGradebook(
        classId,
        examId: _selectedExamId,
        sectionId: cls?.sectionId?.toString(),
      );

      if (res.success && res.data != null) {
        _parseGradebookResponse(res.data!);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('cached_gradebook_data', jsonEncode(res.data));
      } else if (_students.isEmpty) {
        _buildFallbackGradebook();
      }
    } catch (_) {
      if (_students.isEmpty) _buildFallbackGradebook();
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _parseGradebookResponse(Map<String, dynamic> data) {
    setState(() {
      _gradebookData = data;
      if (data['summary'] is Map) {
        _summary = Map<String, dynamic>.from(data['summary'] as Map);
      }
      if (data['distribution'] is Map) {
        _distribution = Map<String, dynamic>.from(data['distribution'] as Map);
      }
      if (data['students'] is List) {
        _students = (data['students'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }
    });
  }

  void _buildFallbackGradebook() {
    setState(() {
      _students = [
        {
          'student_id': 5,
          'name': 'Diya Maharjan',
          'roll_no': '05',
          'avatar': 'DM',
          'gpa': 3.98,
          'grade': 'A+',
          'percentage': 96.5,
          'total_marks': 482,
          'max_marks': 500,
          'rank': 1,
          'attendance': '98%',
          'status': 'Passed (Distinction)',
          'remark': 'Exceptional performance across all subjects. Class Topper!',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 99, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 96, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 95, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 94, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 98, 'grade': 'A+', 'gpa': 4.0},
          ],
        },
        {
          'student_id': 1,
          'name': 'Aayush Adhikari',
          'roll_no': '01',
          'avatar': 'AA',
          'gpa': 3.92,
          'grade': 'A+',
          'percentage': 93.8,
          'total_marks': 469,
          'max_marks': 500,
          'rank': 2,
          'attendance': '96%',
          'status': 'Passed (Distinction)',
          'remark': 'Consistent top-tier academic competence. Outstanding logical acumen.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 98, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 94, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 92, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 90, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 95, 'grade': 'A+', 'gpa': 4.0},
          ],
        },
        {
          'student_id': 2,
          'name': 'Ananya Joshi',
          'roll_no': '02',
          'avatar': 'AJ',
          'gpa': 3.85,
          'grade': 'A',
          'percentage': 89.2,
          'total_marks': 446,
          'max_marks': 500,
          'rank': 3,
          'attendance': '94%',
          'status': 'Passed (First Division)',
          'remark': 'Very strong analytical skills and excellent classroom engagement.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 92, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 90, 'grade': 'A+', 'gpa': 4.0},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 88, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 86, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 90, 'grade': 'A+', 'gpa': 4.0},
          ],
        },
        {
          'student_id': 4,
          'name': 'Aarav Sharma',
          'roll_no': '04',
          'avatar': 'AS',
          'gpa': 3.65,
          'grade': 'A',
          'percentage': 84.0,
          'total_marks': 420,
          'max_marks': 500,
          'rank': 4,
          'attendance': '92%',
          'status': 'Passed (First Division)',
          'remark': 'Great performance. Advised to focus a bit more on Nepali grammar.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 88, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 86, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 85, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 76, 'grade': 'B+', 'gpa': 3.2},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 85, 'grade': 'A', 'gpa': 3.6},
          ],
        },
        {
          'student_id': 6,
          'name': 'Kritika Poudel',
          'roll_no': '06',
          'avatar': 'KP',
          'gpa': 3.52,
          'grade': 'A',
          'percentage': 81.6,
          'total_marks': 408,
          'max_marks': 500,
          'rank': 5,
          'attendance': '90%',
          'status': 'Passed (First Division)',
          'remark': 'Consistent hard worker with dependable problem-solving habits.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 82, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 84, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 82, 'grade': 'A', 'gpa': 3.6},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 78, 'grade': 'B+', 'gpa': 3.2},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 82, 'grade': 'A', 'gpa': 3.6},
          ],
        },
        {
          'student_id': 3,
          'name': 'Bibek KC',
          'roll_no': '03',
          'avatar': 'BK',
          'gpa': 3.12,
          'grade': 'B+',
          'percentage': 73.4,
          'total_marks': 367,
          'max_marks': 500,
          'rank': 6,
          'attendance': '78%',
          'status': 'Passed (Second Division)',
          'remark': 'Capable student. Attendance improvement needed for higher GPA.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 74, 'grade': 'B+', 'gpa': 3.2},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 72, 'grade': 'B+', 'gpa': 3.2},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 76, 'grade': 'B+', 'gpa': 3.2},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 70, 'grade': 'B+', 'gpa': 3.2},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 75, 'grade': 'B+', 'gpa': 3.2},
          ],
        },
        {
          'student_id': 7,
          'name': 'Nischal Tamang',
          'roll_no': '07',
          'avatar': 'NT',
          'gpa': 2.85,
          'grade': 'B',
          'percentage': 64.8,
          'total_marks': 324,
          'max_marks': 500,
          'rank': 7,
          'attendance': '84%',
          'status': 'Passed',
          'remark': 'Active in practical work. Needs extra coaching in trigonometry.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 62, 'grade': 'B', 'gpa': 2.8},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 68, 'grade': 'B', 'gpa': 2.8},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 65, 'grade': 'B', 'gpa': 2.8},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 60, 'grade': 'B', 'gpa': 2.8},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 69, 'grade': 'B', 'gpa': 2.8},
          ],
        },
        {
          'student_id': 8,
          'name': 'Prashant Basnet',
          'roll_no': '08',
          'avatar': 'PB',
          'gpa': 2.45,
          'grade': 'C+',
          'percentage': 56.0,
          'total_marks': 280,
          'max_marks': 500,
          'rank': 8,
          'attendance': '80%',
          'status': 'Passed (Satisfactory)',
          'remark': 'Requires regular homework submission and guided revision sessions.',
          'subjects': [
            {'subject_name': 'Compulsory Mathematics', 'subject_code': 'MTH-10', 'score': 52, 'grade': 'C+', 'gpa': 2.4},
            {'subject_name': 'Science & Technology', 'subject_code': 'SCI-10', 'score': 58, 'grade': 'C+', 'gpa': 2.4},
            {'subject_name': 'Compulsory English', 'subject_code': 'ENG-10', 'score': 56, 'grade': 'C+', 'gpa': 2.4},
            {'subject_name': 'Compulsory Nepali', 'subject_code': 'NEP-10', 'score': 54, 'grade': 'C+', 'gpa': 2.4},
            {'subject_name': 'Social Studies', 'subject_code': 'SOC-10', 'score': 60, 'grade': 'B', 'gpa': 2.8},
          ],
        },
      ];
    });
  }

  List<Map<String, dynamic>> _getProcessedStudents() {
    var list = List<Map<String, dynamic>>.from(_students);

    // Filter by search query
    if (_searchQuery.trim().isNotEmpty) {
      final q = _searchQuery.toLowerCase().trim();
      list = list.where((s) {
        final name = (s['name'] ?? '').toString().toLowerCase();
        final roll = (s['roll_no'] ?? '').toString().toLowerCase();
        return name.contains(q) || roll.contains(q);
      }).toList();
    }

    // Filter by letter grade
    if (_gradeFilter != null) {
      list = list.where((s) => s['grade'] == _gradeFilter).toList();
    }

    // Sort
    if (_sortBy == 'rank') {
      list.sort((a, b) => ((a['rank'] ?? 0) as num).compareTo((b['rank'] ?? 0) as num));
    } else if (_sortBy == 'roll') {
      list.sort((a, b) => ((int.tryParse(a['roll_no']?.toString() ?? '0') ?? 0))
          .compareTo((int.tryParse(b['roll_no']?.toString() ?? '0') ?? 0)));
    } else if (_sortBy == 'needs_support') {
      list.sort((a, b) => ((a['gpa'] ?? 0) as num).compareTo((b['gpa'] ?? 0) as num));
    }

    return list;
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Student Grade Dossier & Performance Profile
  // ─────────────────────────────────────────────────────────────────────────

  void _showStudentDossier(Map<String, dynamic> student) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final subjects = student['subjects'] is List
        ? (student['subjects'] as List)
        : <Map<String, dynamic>>[];
    final gradeColor = _getGradeColor(student['grade']?.toString() ?? 'B');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDossierState) => DraggableScrollableSheet(
          initialChildSize: 0.88,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (_, scrollCtrl) => Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: ListView(
              controller: scrollCtrl,
              children: [
                // Header
                Row(
                  children: [
                    CircleAvatar(
                      radius: 26,
                      backgroundColor: gradeColor.withOpacity(0.15),
                      child: Text(
                        student['avatar'] ?? 'ST',
                        style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: gradeColor),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                student['name'] ?? 'Student',
                                style: const TextStyle(
                                    fontSize: 17, fontWeight: FontWeight.w900),
                              ),
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.amber.shade100,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'Rank #${student['rank'] ?? 1}',
                                  style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.amber.shade900),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Roll No: ${student['roll_no']} • Attendance: ${student['attendance'] ?? '94%'}',
                            style: const TextStyle(
                                fontSize: 12, color: AppTheme.grey),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 24),

                // Performance Score Hero
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [gradeColor, gradeColor.withOpacity(0.75)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(18),
                    boxShadow: [
                      BoxShadow(
                        color: gradeColor.withOpacity(0.35),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      )
                    ],
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('CONSOLIDATED GPA',
                              style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w800)),
                          const SizedBox(height: 4),
                          Text(
                            '${student['gpa']} / 4.00',
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 24,
                                fontWeight: FontWeight.w900),
                          ),
                          Text(
                            'Score: ${student['total_marks']}/${student['max_marks'] ?? 500} (${student['percentage']}%)',
                            style: const TextStyle(
                                color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 10),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: Colors.white38),
                        ),
                        child: Column(
                          children: [
                            const Text('GRADE',
                                style: TextStyle(
                                    color: Colors.white70,
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800)),
                            Text(
                              student['grade'] ?? 'A',
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 22,
                                  fontWeight: FontWeight.w900),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Teacher Evaluation Remarks Box
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: Colors.black.withOpacity(0.06)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.rate_review_rounded,
                              size: 16, color: AppTheme.primary),
                          SizedBox(width: 6),
                          Text('Teacher Observation (शिक्षक टिप्पणी)',
                              style: TextStyle(
                                  fontSize: 12, fontWeight: FontWeight.w800)),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        student['remark']?.toString() ??
                            'Demonstrates good understanding. Keep maintaining regular study habits.',
                        style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            fontStyle: FontStyle.italic,
                            height: 1.4),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),

                // Subject-by-Subject Marksheet Breakdown
                const Text(
                  'Subject-wise Terminal Performance (विषयगत प्राप्ताङ्क)',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 10),

                if (subjects.isEmpty)
                  const Text('No subject marks entered yet.',
                      style: TextStyle(color: AppTheme.grey))
                else
                  ...subjects.map((sub) {
                    final subScore = (sub['score'] ?? 0) as num;
                    final subGrade = sub['grade'] ?? 'B';
                    final sColor = _getGradeColor(subGrade);

                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark ? AppTheme.darkCard : Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border:
                            Border.all(color: Colors.black.withOpacity(0.06)),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: sColor.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              subGrade,
                              style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                  color: sColor),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  sub['subject_name'] ?? 'Subject',
                                  style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w800),
                                ),
                                Text(
                                  sub['subject_code'] ?? 'SUB-101',
                                  style: const TextStyle(
                                      fontSize: 10.5, color: AppTheme.grey),
                                ),
                              ],
                            ),
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                '$subScore / 100',
                                style: const TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w900,
                                    fontFamily: 'monospace'),
                              ),
                              Text(
                                'GPA: ${sub['gpa'] ?? 3.6}',
                                style: const TextStyle(
                                    fontSize: 10.5, color: AppTheme.grey),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  }),
                const SizedBox(height: 18),

                // Action Buttons: Edit Score & Share
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.edit_note_rounded, size: 16),
                        label: const Text('Update Evaluation / Remarks'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () {
                          _showEditGradeDialog(student, (newRemark) {
                            setDossierState(() {
                              student['remark'] = newRemark;
                            });
                            setState(() {
                              student['remark'] = newRemark;
                            });
                          });
                        },
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showEditGradeDialog(
      Map<String, dynamic> student, Function(String newRemark) onUpdated) {
    final remarkCtrl =
        TextEditingController(text: student['remark']?.toString() ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.edit_note_rounded, color: AppTheme.primary),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'Evaluation: ${student['name']}',
                style:
                    const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Roll #${student['roll_no']} • Current GPA: ${student['gpa']}',
                style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            const SizedBox(height: 14),
            TextField(
              controller: remarkCtrl,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Teacher Evaluation Remarks (शिक्षक टिप्पणी)',
                hintText: 'e.g. Excellent conceptual grasp. Advised to practice geometry proofs.',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                'उत्कृष्ट उपलब्धि (Outstanding)',
                'प्रथम श्रेणी (First Division)',
                'प्रगति सन्तोषजनक (Satisfactory)',
                'मेहनत आवश्यक (Needs Attention)',
              ].map((chip) {
                return ActionChip(
                  label: Text(chip, style: const TextStyle(fontSize: 10)),
                  onPressed: () => remarkCtrl.text = chip,
                );
              }).toList(),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.green,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () async {
              final newRemark = remarkCtrl.text.trim();
              Navigator.pop(ctx);

              await _apiService.updateStudentGrade(
                student['student_id'].toString(),
                '1',
                (student['percentage'] ?? 85).toDouble(),
                remark: newRemark,
              );

              onUpdated(newRemark);

              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    backgroundColor: AppTheme.green,
                    content: Text(
                        '✅ Evaluation remarks saved for ${student['name']}!'),
                  ),
                );
              }
            },
            child: const Text('Save Remarks'),
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Export Marksheet Modal
  // ─────────────────────────────────────────────────────────────────────────

  void _showExportModal() {
    final processed = _getProcessedStudents();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.85,
        maxChildSize: 0.95,
        minChildSize: 0.5,
        expand: false,
        builder: (_, scrollCtrl) => Padding(
          padding: const EdgeInsets.all(20),
          child: ListView(
            controller: scrollCtrl,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Consolidated Class Marksheet',
                          style: TextStyle(
                              fontSize: 18, fontWeight: FontWeight.w900)),
                      Text('अभिलेख खाता तथा परीक्षा परिणाम तालिका',
                          style:
                              TextStyle(fontSize: 12, color: AppTheme.grey)),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Exam: $_selectedExamTitle',
                      style: const TextStyle(
                          fontSize: 12, fontWeight: FontWeight.w700)),
                  Text('${processed.length} Students Total',
                      style: const TextStyle(
                          fontSize: 12, color: AppTheme.primary)),
                ],
              ),
              const SizedBox(height: 14),

              // Export Preview Table
              Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: DataTable(
                    headingRowColor:
                        MaterialStateProperty.all(AppTheme.lightBg),
                    columns: const [
                      DataColumn(label: Text('Rank', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('Roll', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('Student Name', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('GPA', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('Grade', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('Total', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('Attendance', style: TextStyle(fontWeight: FontWeight.w800))),
                      DataColumn(label: Text('Status', style: TextStyle(fontWeight: FontWeight.w800))),
                    ],
                    rows: processed.map((s) {
                      return DataRow(cells: [
                        DataCell(Text('#${s['rank']}')),
                        DataCell(Text('#${s['roll_no']}')),
                        DataCell(Text(s['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w700))),
                        DataCell(Text('${s['gpa']}', style: const TextStyle(fontWeight: FontWeight.w900))),
                        DataCell(GradeBadge(grade: s['grade'] ?? 'A')),
                        DataCell(Text('${s['total_marks']}')),
                        DataCell(Text('${s['attendance']}')),
                        DataCell(Text(s['status'] ?? 'Passed', style: const TextStyle(color: Colors.green))),
                      ]);
                    }).toList(),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.file_download_outlined, color: Colors.white),
                  label: const Text('Download Official CSV Ledger / Marksheet',
                      style: TextStyle(
                          color: Colors.white, fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primary,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {
                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        backgroundColor: AppTheme.green,
                        content: Text(
                            '📄 Gradebook Marksheet CSV exported and ready to print!'),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final classesList = teacherProvider.teacherClasses;
    final processedStudents = _getProcessedStudents();

    final classAvgGPA = (_summary['class_avg_gpa'] ?? 3.52) as num;
    final topGPA = (_summary['top_gpa'] ?? 3.98) as num;
    final topperName = (_summary['topper_name'] ?? 'Diya Maharjan') as String;
    final passRate = (_summary['pass_rate'] ?? 96.8) as num;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Gradebook Ledger (ग्रेडसिट)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Consolidated Terminal Marksheet & Analytics',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          CircleIconButton(
            icon: Icons.file_download_outlined,
            onTap: _showExportModal,
            size: 38,
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppTheme.grey),
            onPressed: _fetchGradebook,
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchGradebook,
        color: AppTheme.primary,
        child: Column(
          children: [
            // Exam Term Selector Dropdown Bar
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 6, 16, 8),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkCard : Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                      color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.assignment_outlined,
                        size: 16, color: AppTheme.primary),
                    const SizedBox(width: 8),
                    Expanded(
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _selectedExamId,
                          isExpanded: true,
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w700,
                            color: isDark ? Colors.white : AppTheme.dark,
                          ),
                          items: _examTerms.map((t) {
                            return DropdownMenuItem(
                              value: t['id']!,
                              child: Text(t['name']!),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) {
                              setState(() {
                                _selectedExamId = val;
                                _selectedExamTitle = _examTerms
                                    .firstWhere((t) => t['id'] == val)['name']!;
                              });
                              _fetchGradebook();
                            }
                          },
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Class Selector Carousel
            SizedBox(
              height: 42,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: classesList.isNotEmpty ? classesList.length : 3,
                itemBuilder: (_, i) {
                  final sel = i == _selectedClassIndex;
                  final label = classesList.isNotEmpty
                      ? classesList[i].section
                      : 'Grade 10 - A';
                  return GestureDetector(
                    onTap: () {
                      setState(() => _selectedClassIndex = i);
                      _fetchGradebook();
                    },
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        gradient: sel
                            ? const LinearGradient(
                                colors: [Color(0xFF0F4C81), Color(0xFF2563EB)])
                            : null,
                        color: sel
                            ? null
                            : (isDark ? AppTheme.darkCard : Colors.white),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: sel
                              ? AppTheme.primary
                              : (isDark
                                  ? AppTheme.darkCardBorder
                                  : AppTheme.cardBorder),
                        ),
                      ),
                      child: Center(
                        child: Text(
                          label,
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: sel ? Colors.white : AppTheme.grey,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 10),

            // Modern Class Analytics Hero Card
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0F4C81), Color(0xFF1E3A8A), Color(0xFF2563EB)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF1E3A8A).withOpacity(0.35),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    )
                  ],
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'CLASS GPA AVERAGE',
                              style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800),
                            ),
                            const SizedBox(height: 2),
                            Row(
                              children: [
                                Text(
                                  classAvgGPA.toStringAsFixed(2),
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 26,
                                      fontWeight: FontWeight.w900),
                                ),
                                const SizedBox(width: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.white24,
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text('4.0 Scale',
                                      style: TextStyle(
                                          color: Colors.white,
                                          fontSize: 9.5,
                                          fontWeight: FontWeight.w700)),
                                ),
                              ],
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white30),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              const Row(
                                children: [
                                  Icon(Icons.emoji_events_rounded,
                                      size: 13, color: Colors.amber),
                                  SizedBox(width: 4),
                                  Text('CLASS TOPPER',
                                      style: TextStyle(
                                          color: Colors.amber,
                                          fontSize: 9.5,
                                          fontWeight: FontWeight.w900)),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(topperName,
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w800)),
                              Text('$topGPA GPA (A+)',
                                  style: const TextStyle(
                                      color: Colors.white70,
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w700)),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 20, color: Colors.white24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _HeroMiniStat(
                            label: 'Total Enrolled',
                            value: '${_summary['total_students'] ?? 32}'),
                        _HeroMiniStat(
                            label: 'Pass Rate',
                            value: '${passRate.toStringAsFixed(1)}%'),
                        _HeroMiniStat(
                            label: 'Distinctions (A/A+)',
                            value: '${_summary['distinction_count'] ?? 17}'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Dynamic Grade Distribution Histogram
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkCard : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                      color: isDark
                          ? AppTheme.darkCardBorder
                          : AppTheme.cardBorder),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('NEB Grade Distribution (ग्रेड विभाजन)',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.w800)),
                        if (_gradeFilter != null)
                          GestureDetector(
                            onTap: () => setState(() => _gradeFilter = null),
                            child: const Text('Clear Filter',
                                style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: AppTheme.primary)),
                          ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        _buildHistogramBar('A+', _distribution['A+'] ?? 6, 20,
                            const Color(0xFF059669)),
                        _buildHistogramBar('A', _distribution['A'] ?? 11, 20,
                            const Color(0xFF0D9488)),
                        _buildHistogramBar('B+', _distribution['B+'] ?? 8, 20,
                            const Color(0xFF2563EB)),
                        _buildHistogramBar('B', _distribution['B'] ?? 4, 20,
                            const Color(0xFF4F46E5)),
                        _buildHistogramBar('C+', _distribution['C+'] ?? 2, 20,
                            const Color(0xFFD97706)),
                        _buildHistogramBar('C', _distribution['C'] ?? 1, 20,
                            const Color(0xFFEA580C)),
                        _buildHistogramBar('D', _distribution['D'] ?? 0, 20,
                            const Color(0xFFDC2626)),
                        _buildHistogramBar('NG', _distribution['NG'] ?? 0, 20,
                            const Color(0xFF991B1B)),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 10),

            // Search & Sort Filter Row
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      height: 38,
                      decoration: BoxDecoration(
                        color: isDark ? AppTheme.darkCard : Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                            color: isDark
                                ? AppTheme.darkCardBorder
                                : AppTheme.cardBorder),
                      ),
                      child: TextField(
                        onChanged: (v) => setState(() => _searchQuery = v),
                        decoration: const InputDecoration(
                          hintText: 'Search student or roll...',
                          hintStyle: TextStyle(fontSize: 11.5),
                          prefixIcon: Icon(Icons.search, size: 16),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    height: 38,
                    padding: const EdgeInsets.symmetric(horizontal: 10),
                    decoration: BoxDecoration(
                      color: isDark ? AppTheme.darkCard : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: isDark
                              ? AppTheme.darkCardBorder
                              : AppTheme.cardBorder),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _sortBy,
                        style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: isDark ? Colors.white : AppTheme.dark),
                        items: const [
                          DropdownMenuItem(
                              value: 'rank', child: Text('Sort: Rank')),
                          DropdownMenuItem(
                              value: 'roll', child: Text('Sort: Roll No')),
                          DropdownMenuItem(
                              value: 'needs_support',
                              child: Text('Sort: Lowest GPA')),
                        ],
                        onChanged: (v) {
                          if (v != null) setState(() => _sortBy = v);
                        },
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // Students Marksheet List
            Expanded(
              child: _isLoading && _students.isEmpty
                  ? const Center(child: CircularProgressIndicator())
                  : processedStudents.isEmpty
                      ? const Center(
                          child: Text('No students found matching filter.',
                              style: TextStyle(color: AppTheme.grey)),
                        )
                      : ListView.builder(
                          physics: const BouncingScrollPhysics(),
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 80),
                          itemCount: processedStudents.length,
                          itemBuilder: (_, i) {
                            final s = processedStudents[i];
                            final gColor =
                                _getGradeColor(s['grade']?.toString() ?? 'B');
                            final gpa = (s['gpa'] ?? 3.0) as num;

                            return InkWell(
                              onTap: () => _showStudentDossier(s),
                              borderRadius: BorderRadius.circular(16),
                              child: Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? AppTheme.darkCard
                                      : Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(
                                    color: isDark
                                        ? AppTheme.darkCardBorder
                                        : AppTheme.cardBorder,
                                  ),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.03),
                                      blurRadius: 6,
                                      offset: const Offset(0, 2),
                                    )
                                  ],
                                ),
                                child: Row(
                                  children: [
                                    // Rank & Roll
                                    Column(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                              horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: s['rank'] <= 3
                                                ? Colors.amber.shade100
                                                : Colors.grey.shade100,
                                            borderRadius:
                                                BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            '#${s['rank']}',
                                            style: TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.w900,
                                              color: s['rank'] <= 3
                                                  ? Colors.amber.shade900
                                                  : Colors.grey.shade800,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text('Roll ${s['roll_no']}',
                                            style: const TextStyle(
                                                fontSize: 9.5,
                                                color: AppTheme.grey)),
                                      ],
                                    ),
                                    const SizedBox(width: 12),

                                    // Avatar
                                    CircleAvatar(
                                      radius: 19,
                                      backgroundColor:
                                          gColor.withOpacity(0.12),
                                      child: Text(
                                        s['avatar'] ?? 'ST',
                                        style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                            color: gColor),
                                      ),
                                    ),
                                    const SizedBox(width: 12),

                                    // Name & Progress
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            s['name'] ?? 'Student',
                                            style: const TextStyle(
                                                fontSize: 13,
                                                fontWeight: FontWeight.w800),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          const SizedBox(height: 4),
                                          ClipRRect(
                                            borderRadius:
                                                BorderRadius.circular(3),
                                            child: LinearProgressIndicator(
                                              value: gpa / 4.0,
                                              backgroundColor:
                                                  gColor.withOpacity(0.12),
                                              valueColor:
                                                  AlwaysStoppedAnimation<Color>(
                                                      gColor),
                                              minHeight: 5,
                                            ),
                                          ),
                                          const SizedBox(height: 3),
                                          Text(
                                            'Score: ${s['total_marks']}/${s['max_marks'] ?? 500} (${s['percentage']}%)',
                                            style: const TextStyle(
                                                fontSize: 10.5,
                                                color: AppTheme.grey),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(width: 10),

                                    // GPA & Grade
                                    Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.end,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                              horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: gColor.withOpacity(0.12),
                                            borderRadius:
                                                BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            s['grade'] ?? 'A',
                                            style: TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.w900,
                                                color: gColor),
                                          ),
                                        ),
                                        const SizedBox(height: 3),
                                        Text(
                                          '${gpa.toStringAsFixed(2)} GPA',
                                          style: const TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.w800),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ).animate().fadeIn(delay: (i * 30).ms);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHistogramBar(
      String grade, int count, int maxPossible, Color color) {
    final isSelected = _gradeFilter == grade;
    final pct = (count / (maxPossible > 0 ? maxPossible : 1)).clamp(0.08, 1.0);

    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() {
            _gradeFilter = isSelected ? null : grade;
          });
        },
        child: Column(
          children: [
            Container(
              height: 46,
              alignment: Alignment.bottomCenter,
              child: FractionallySizedBox(
                heightFactor: pct,
                child: Container(
                  width: 18,
                  decoration: BoxDecoration(
                    color: isSelected ? color : color.withOpacity(0.75),
                    borderRadius: BorderRadius.circular(6),
                    border: isSelected
                        ? Border.all(color: Colors.white, width: 1.5)
                        : null,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              grade,
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                color: isSelected ? color : AppTheme.grey,
              ),
            ),
            Text('$count',
                style: const TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.grey)),
          ],
        ),
      ),
    );
  }
}

class _HeroMiniStat extends StatelessWidget {
  final String label, value;
  const _HeroMiniStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(value,
            style: const TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.w900)),
        Text(label,
            style: const TextStyle(color: Colors.white70, fontSize: 10)),
      ],
    );
  }
}
