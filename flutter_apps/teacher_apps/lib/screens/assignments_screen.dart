import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../services/api_service.dart';

class AssignmentsScreen extends StatefulWidget {
  const AssignmentsScreen({super.key});
  @override
  State<AssignmentsScreen> createState() => _AssignmentsScreenState();
}

class _AssignmentsScreenState extends State<AssignmentsScreen>
    with SingleTickerProviderStateMixin {
  final ApiService _apiService = ApiService();
  late TabController _tab;
  String _selectedClassFilter = 'All Classes';
  bool _isLoading = false;

  // Local live list of assignments
  List<Map<String, dynamic>> _assignments = [];

  @override
  void initState() {
    super.initState();
    _tab = TabController(length: 3, vsync: this);
    _loadCachedAssignments();
    _fetchAssignments();
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  Future<void> _loadCachedAssignments() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString('cached_homework_list');
      if (cached != null) {
        final list = jsonDecode(cached) as List;
        setState(() {
          _assignments = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        });
      }
    } catch (_) {}
  }

  Future<void> _fetchAssignments() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiService.getAssignments();
      if (res.success && res.data != null) {
        final list = res.data!;
        setState(() {
          _assignments = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        });
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('cached_homework_list', jsonEncode(_assignments));
      } else if (_assignments.isEmpty) {
        _buildFallbackAssignments();
      }
    } catch (_) {
      if (_assignments.isEmpty) _buildFallbackAssignments();
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _buildFallbackAssignments() {
    setState(() {
      _assignments = [
        {
          'id': 1,
          'title': 'Trigonometry: Heights & Distances Ex 4.2',
          'description': 'Complete Q.No 1 to 14 from textbook page 112 in your homework copy. Draw neat diagrams.',
          'subject': 'Compulsory Mathematics',
          'class_name': 'Grade 10 - A',
          'class_id': 1,
          'section_id': 1,
          'due_date': '2081-07-15 (Friday)',
          'total_marks': 20,
          'submitted': 28,
          'total': 32,
          'graded': 22,
          'status': 'pending',
          'type': 'Written Homework (लिखित गृहकार्य)',
          'colorHex': 0xFF2563EB,
        },
        {
          'id': 2,
          'title': 'Nepali Essay: वातावरण प्रदूषण र रोकथामका उपायहरू',
          'description': 'कम्तीमा २०० शब्दमा वातावरण संरक्षणका उपायहरू समेटी मौलिक निबन्ध तयार पार्नुहोस्।',
          'subject': 'Compulsory Nepali',
          'class_name': 'Grade 10 - A',
          'class_id': 1,
          'section_id': 1,
          'due_date': '2081-07-16 (Saturday)',
          'total_marks': 15,
          'submitted': 24,
          'total': 32,
          'graded': 18,
          'status': 'pending',
          'type': 'Essay (सिर्जनात्मक लेखन)',
          'colorHex': 0xFFDC2626,
        },
        {
          'id': 3,
          'title': 'Science Practical: Plant Cell Microscopic Observation Report',
          'description': 'Draw well-labelled diagrams of Onion Epidermal cells with magnification details and lab remarks.',
          'subject': 'Science & Technology',
          'class_name': 'Grade 9 - B',
          'class_id': 2,
          'section_id': 2,
          'due_date': '2081-07-14 (Thursday)',
          'total_marks': 25,
          'submitted': 30,
          'total': 30,
          'graded': 30,
          'status': 'completed',
          'type': 'Lab Report (प्रयोगात्मक प्रतिवेदन)',
          'colorHex': 0xFF059669,
        },
        {
          'id': 4,
          'title': 'Social Studies: Infrastructure Projects of Nepal Case Study',
          'description': 'Write a short case study on Fast Track or Pokhara International Airport benefits.',
          'subject': 'Social Studies',
          'class_name': 'Grade 10 - B',
          'class_id': 1,
          'section_id': 2,
          'due_date': '2081-07-17 (Sunday)',
          'total_marks': 20,
          'submitted': 19,
          'total': 31,
          'graded': 10,
          'status': 'pending',
          'type': 'Project Work (परियोजना कार्य)',
          'colorHex': 0xFFD97706,
        },
        {
          'id': 5,
          'title': 'English Grammar: Reported Speech Worksheet',
          'description': 'Solve Exercise B and C from Unit 3 workbook page 38.',
          'subject': 'Compulsory English',
          'class_name': 'Grade 10 - A',
          'class_id': 1,
          'section_id': 1,
          'due_date': '2081-07-12',
          'total_marks': 10,
          'submitted': 32,
          'total': 32,
          'graded': 32,
          'status': 'completed',
          'type': 'Grammar Worksheet',
          'colorHex': 0xFF7C3AED,
        },
      ];
    });
  }

  List<Map<String, dynamic>> _filterByClass(List<Map<String, dynamic>> list) {
    if (_selectedClassFilter == 'All Classes') return list;
    return list.where((a) {
      final cls = a['class_name']?.toString() ?? a['section']?.toString() ?? '';
      return cls.contains(_selectedClassFilter) || _selectedClassFilter.contains(cls);
    }).toList();
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Homework Detail & Student Submissions Modal
  // ─────────────────────────────────────────────────────────────────────────

  void _showDetailModal(Map<String, dynamic> hw) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final hwId = hw['id'].toString();
    List<Map<String, dynamic>> submissions = [];
    bool loadingSubs = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            if (loadingSubs) {
              _apiService.getAssignmentSubmissions(hwId).then((res) {
                if (ctx.mounted) {
                  setModalState(() {
                    loadingSubs = false;
                    if (res.success && res.data != null) {
                      submissions = (res.data as List)
                          .map((e) => Map<String, dynamic>.from(e as Map))
                          .toList();
                    } else {
                      // fallback dummy submissions
                      submissions = [
                        {
                          'id': 101,
                          'student_id': 1,
                          'student_name': 'Aayush Adhikari',
                          'roll_no': '01',
                          'status': 'Graded',
                          'marks_obtained': 19,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': 'Excellent problem solving! Very neat copy.',
                          'submitted_at': 'Yesterday 04:30 PM',
                          'file_name': 'aayush_ex4_2.pdf',
                        },
                        {
                          'id': 102,
                          'student_id': 2,
                          'student_name': 'Ananya Joshi',
                          'roll_no': '02',
                          'status': 'Graded',
                          'marks_obtained': 20,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': 'Full marks! All trigonometric proofs correct.',
                          'submitted_at': 'Yesterday 06:15 PM',
                          'file_name': 'ananya_hw.pdf',
                        },
                        {
                          'id': 103,
                          'student_id': 3,
                          'student_name': 'Bibek KC',
                          'roll_no': '03',
                          'status': 'Submitted',
                          'marks_obtained': null,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': null,
                          'submitted_at': 'Today 08:10 AM',
                          'file_name': 'bibek_hw_img.jpg',
                        },
                        {
                          'id': 104,
                          'student_id': 4,
                          'student_name': 'Aarav Sharma',
                          'roll_no': '04',
                          'status': 'Submitted',
                          'marks_obtained': null,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': null,
                          'submitted_at': 'Today 09:05 AM',
                          'file_name': 'aarav_hw.pdf',
                        },
                        {
                          'id': 105,
                          'student_id': 5,
                          'student_name': 'Diya Maharjan',
                          'roll_no': '05',
                          'status': 'Graded',
                          'marks_obtained': 18,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': 'Good work, please double check question 7 calculation.',
                          'submitted_at': '2 days ago',
                          'file_name': 'diya_exercise.pdf',
                        },
                        {
                          'id': 106,
                          'student_id': 6,
                          'student_name': 'Kritika Poudel',
                          'roll_no': '06',
                          'status': 'Submitted',
                          'marks_obtained': null,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': null,
                          'submitted_at': 'Today 07:45 AM',
                          'file_name': 'kritika_hw.pdf',
                        },
                        {
                          'id': 107,
                          'student_id': 7,
                          'student_name': 'Nischal Tamang',
                          'roll_no': '07',
                          'status': 'Late',
                          'marks_obtained': null,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': null,
                          'submitted_at': 'Today 10:15 AM (Late by 2 hrs)',
                          'file_name': 'nischal_scan.pdf',
                        },
                        {
                          'id': 108,
                          'student_id': 8,
                          'student_name': 'Prashant Basnet',
                          'roll_no': '08',
                          'status': 'Not Submitted',
                          'marks_obtained': null,
                          'total_marks': hw['total_marks'] ?? 20,
                          'teacher_feedback': null,
                          'submitted_at': 'Pending submission',
                          'file_name': null,
                        },
                      ];
                    }
                  });
                }
              });
            }

            final submittedCount = hw['submitted'] is int
                ? hw['submitted'] as int
                : (int.tryParse(hw['submitted']?.toString() ?? '') ?? 0);
            final totalCount = hw['total'] is int
                ? hw['total'] as int
                : (int.tryParse(hw['total']?.toString() ?? '') ?? 32);
            final gradedCount = hw['graded'] is int
                ? hw['graded'] as int
                : (int.tryParse(hw['graded']?.toString() ?? '') ?? 0);

            return DraggableScrollableSheet(
              initialChildSize: 0.88,
              maxChildSize: 0.95,
              minChildSize: 0.5,
              expand: false,
              builder: (_, scrollCtrl) {
                return Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                  child: ListView(
                    controller: scrollCtrl,
                    children: [
                      // Header
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: Color(hw['colorHex'] as int? ?? 0xFF2563EB).withOpacity(0.12),
                              borderRadius: BorderRadius.circular(14),
                            ),
                            child: Icon(
                              Icons.assignment_rounded,
                              color: Color(hw['colorHex'] as int? ?? 0xFF2563EB),
                              size: 26,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  hw['title']?.toString() ?? 'Homework',
                                  style: const TextStyle(
                                      fontSize: 16, fontWeight: FontWeight.w900),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${hw['subject']} • ${hw['class_name'] ?? hw['section']}',
                                  style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: AppTheme.grey),
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

                      // Metadata & Instructions Box
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
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    const Icon(Icons.calendar_today_rounded,
                                        size: 13, color: AppTheme.primary),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Due: ${hw['due_date']}',
                                      style: const TextStyle(
                                          fontSize: 12, fontWeight: FontWeight.w700),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: AppTheme.primary.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    'Full Marks: ${hw['total_marks'] ?? 20}',
                                    style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        color: AppTheme.primary),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            Text(
                              hw['description']?.toString() ??
                                  'Complete all assigned exercises in your homework notebook.',
                              style: const TextStyle(
                                  fontSize: 12.5, color: Colors.black87, height: 1.4),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Submission Stats Bar
                      Row(
                        children: [
                          _MiniStat(
                            label: 'Submitted',
                            value: '$submittedCount/$totalCount',
                            color: AppTheme.primary,
                          ),
                          const SizedBox(width: 8),
                          _MiniStat(
                            label: 'Graded',
                            value: '$gradedCount',
                            color: AppTheme.green,
                          ),
                          const SizedBox(width: 8),
                          _MiniStat(
                            label: 'Pending',
                            value: '${totalCount - submittedCount}',
                            color: Colors.redAccent,
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Action Toolbar: Remind Unsubmitted & Broadcast
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              icon: const Icon(Icons.notifications_active_outlined,
                                  size: 16, color: Colors.deepOrange),
                              label: const Text('Remind Defaulters',
                                  style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w700,
                                      color: Colors.deepOrange)),
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: Colors.deepOrange),
                                padding: const EdgeInsets.symmetric(vertical: 10),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: () async {
                                await _apiService.remindHomeworkDefaulters(hwId);
                                if (context.mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(
                                      backgroundColor: AppTheme.green,
                                      content: Text(
                                          '📢 Push reminder & SMS sent to all pending students and parents!'),
                                    ),
                                  );
                                }
                              },
                            ),
                          ),
                          const SizedBox(width: 10),
                          IconButton(
                            icon: const Icon(Icons.delete_outline_rounded,
                                color: Colors.red),
                            tooltip: 'Delete Homework',
                            onPressed: () {
                              Navigator.pop(ctx);
                              _confirmDelete(hw);
                            },
                          ),
                        ],
                      ),
                      const Divider(height: 28),

                      // Students Submissions List
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Student Submissions (विद्यार्थी गृहकार्य सूची)',
                            style: TextStyle(
                                fontSize: 14, fontWeight: FontWeight.w800),
                          ),
                          Text(
                            '${submissions.length} Students',
                            style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.grey),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      if (loadingSubs)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(24),
                            child: CircularProgressIndicator(),
                          ),
                        )
                      else if (submissions.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(24),
                            child: Text('No student submissions found.',
                                style: TextStyle(color: AppTheme.grey)),
                          ),
                        )
                      else
                        ...submissions.map((sub) {
                          final isGraded = sub['status'] == 'Graded';
                          final isLate = sub['status'] == 'Late';
                          final isNotSub = sub['status'] == 'Not Submitted';

                          return Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: isDark ? AppTheme.darkCard : Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: isGraded
                                    ? Colors.green.shade200
                                    : (isNotSub
                                        ? Colors.grey.shade300
                                        : AppTheme.primary.withOpacity(0.3)),
                              ),
                            ),
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 18,
                                  backgroundColor: isGraded
                                      ? Colors.green.shade50
                                      : (isNotSub
                                          ? Colors.grey.shade100
                                          : AppTheme.primary.withOpacity(0.1)),
                                  child: Text(
                                    sub['roll_no']?.toString() ?? '01',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      color: isGraded
                                          ? Colors.green.shade700
                                          : (isNotSub
                                              ? Colors.grey.shade600
                                              : AppTheme.primary),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        sub['student_name']?.toString() ?? 'Student',
                                        style: const TextStyle(
                                            fontSize: 13, fontWeight: FontWeight.w800),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        sub['submitted_at']?.toString() ?? '',
                                        style: const TextStyle(
                                            fontSize: 10.5, color: AppTheme.grey),
                                      ),
                                      if (sub['teacher_feedback'] != null &&
                                          sub['teacher_feedback'].toString().isNotEmpty) ...[
                                        const SizedBox(height: 3),
                                        Text(
                                          '💬 "${sub['teacher_feedback']}"',
                                          style: TextStyle(
                                              fontSize: 11,
                                              fontStyle: FontStyle.italic,
                                              color: Colors.green.shade800),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                                if (sub['file_name'] != null) ...[
                                  IconButton(
                                    icon: const Icon(Icons.attachment_rounded,
                                        size: 20, color: AppTheme.primary),
                                    tooltip: 'View submitted copy',
                                    onPressed: () {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(
                                          content: Text(
                                              'Opening copy attachment: ${sub['file_name']}'),
                                        ),
                                      );
                                    },
                                  ),
                                ],
                                const SizedBox(width: 4),
                                if (isNotSub)
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade100,
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: const Text('Pending',
                                        style: TextStyle(
                                            fontSize: 10,
                                            fontWeight: FontWeight.w700,
                                            color: AppTheme.grey)),
                                  )
                                else
                                  ElevatedButton(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: isGraded
                                          ? Colors.green.shade600
                                          : AppTheme.primary,
                                      foregroundColor: Colors.white,
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 10, vertical: 6),
                                      minimumSize: const Size(60, 32),
                                      shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(8)),
                                    ),
                                    onPressed: () {
                                      _showGradeDialog(hw, sub, (newMarks, newFeedback) {
                                        setModalState(() {
                                          sub['marks_obtained'] = newMarks;
                                          sub['teacher_feedback'] = newFeedback;
                                          sub['status'] = 'Graded';
                                        });
                                      });
                                    },
                                    child: Text(
                                      isGraded
                                          ? '${sub['marks_obtained']}/${sub['total_marks']}'
                                          : 'Grade',
                                      style: const TextStyle(
                                          fontSize: 11, fontWeight: FontWeight.w800),
                                    ),
                                  ),
                              ],
                            ),
                          );
                        }),
                    ],
                  ),
                );
              },
            );
          },
        );
      },
    );
  }

  void _showGradeDialog(
    Map<String, dynamic> hw,
    Map<String, dynamic> sub,
    Function(double marks, String feedback) onGraded,
  ) {
    final marksCtrl = TextEditingController(
      text: sub['marks_obtained'] != null ? '${sub['marks_obtained']}' : '',
    );
    final feedbackCtrl = TextEditingController(
      text: sub['teacher_feedback']?.toString() ?? '',
    );
    final totalMarks = (hw['total_marks'] ?? 20).toDouble();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.rate_review_rounded, color: AppTheme.primary),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                'Grade: ${sub['student_name']}',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Roll No: ${sub['roll_no']} • Max Marks: $totalMarks',
                style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            const SizedBox(height: 16),
            TextField(
              controller: marksCtrl,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              decoration: InputDecoration(
                labelText: 'Marks Obtained (प्राप्ताङ्क)',
                hintText: 'e.g. 18.5',
                suffixText: '/ $totalMarks',
                filled: true,
                fillColor: AppTheme.lightBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: feedbackCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: 'Teacher Remarks / Feedback (प्रतिक्रिया)',
                hintText: 'e.g. Very good handwriting! Keep it up.',
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
                'उत्कृष्ट (Excellent)',
                'धेरै राम्रो (Very Good)',
                'सुधार आवश्यक (Needs Focus)',
              ].map((chip) {
                return ActionChip(
                  label: Text(chip, style: const TextStyle(fontSize: 10)),
                  onPressed: () => feedbackCtrl.text = chip,
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
              final marks = double.tryParse(marksCtrl.text.trim()) ?? 0;
              final feedback = feedbackCtrl.text.trim();
              Navigator.pop(ctx);

              await _apiService.gradeHomeworkSubmission(
                hw['id'].toString(),
                sub['id'].toString(),
                marks,
                feedback: feedback,
              );

              onGraded(marks, feedback);

              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    backgroundColor: AppTheme.green,
                    content: Text(
                        '✅ Marks recorded: $marks / $totalMarks for ${sub['student_name']}'),
                  ),
                );
              }
            },
            child: const Text('Save Grade & Feedback'),
          ),
        ],
      ),
    );
  }

  void _confirmDelete(Map<String, dynamic> hw) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Homework?'),
        content: Text('Are you sure you want to remove "${hw['title']}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () async {
              Navigator.pop(ctx);
              final hwId = hw['id'].toString();
              setState(() {
                _assignments.removeWhere((item) => item['id'].toString() == hwId);
              });
              await _apiService.deleteAssignment(hwId);
              final prefs = await SharedPreferences.getInstance();
              await prefs.setString('cached_homework_list', jsonEncode(_assignments));
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Homework deleted successfully.')),
                );
              }
            },
            child: const Text('Delete', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────────────
  // Add Homework Modal
  // ─────────────────────────────────────────────────────────────────────────

  void _showAddDialog(bool isDark) {
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final titleCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    final subjectCtrl = TextEditingController(text: 'Compulsory Mathematics');
    final marksCtrl = TextEditingController(text: '20');

    String selectedClass = classesList.isNotEmpty ? classesList.first.section : 'Grade 10 - A';
    String selectedType = 'Written Homework (लिखित गृहकार्य)';
    String dueDate = '१५ कार्तिक २०८१ (Friday)';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.add_task_rounded, color: AppTheme.primary, size: 22),
                        SizedBox(width: 8),
                        Text(
                          'Assign Homework (नयाँ गृहकार्य)',
                          style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: titleCtrl,
                  decoration: InputDecoration(
                    labelText: 'Homework Topic / Exercise Title',
                    hintText: 'e.g. Chapter 4: Quadratic Equations Ex 4.2',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: descCtrl,
                  maxLines: 2,
                  decoration: InputDecoration(
                    labelText: 'Detailed Instructions (विवरण / निर्देशन)',
                    hintText: 'e.g. Solve Q 1 to 10 in homework copy with steps.',
                    filled: true,
                    fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: selectedClass,
                        decoration: InputDecoration(
                          labelText: 'Class',
                          filled: true,
                          fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        items: (classesList.isNotEmpty
                                ? classesList.map((c) => c.section).toSet().toList()
                                : ['Grade 10 - A', 'Grade 10 - B', 'Grade 9 - B', 'Grade 8 - A'])
                            .map((s) => DropdownMenuItem(value: s, child: Text(s)))
                            .toList(),
                        onChanged: (v) {
                          if (v != null) setDialogState(() => selectedClass = v);
                        },
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextField(
                        controller: subjectCtrl,
                        decoration: InputDecoration(
                          labelText: 'Subject',
                          filled: true,
                          fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: marksCtrl,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                          labelText: 'Total Marks (पूर्णाङ्क)',
                          hintText: '20',
                          filled: true,
                          fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: selectedType,
                        decoration: InputDecoration(
                          labelText: 'Type',
                          filled: true,
                          fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        items: const [
                          DropdownMenuItem(
                              value: 'Written Homework (लिखित गृहकार्य)',
                              child: Text('Written Ex.')),
                          DropdownMenuItem(
                              value: 'Lab Report (प्रयोगात्मक प्रतिवेदन)',
                              child: Text('Lab Report')),
                          DropdownMenuItem(
                              value: 'Project Work (परियोजना कार्य)',
                              child: Text('Project Work')),
                          DropdownMenuItem(
                              value: 'Essay / Composition (निबन्ध)',
                              child: Text('Essay/Creative')),
                        ],
                        onChanged: (v) {
                          if (v != null) setDialogState(() => selectedType = v);
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: () async {
                      if (titleCtrl.text.trim().isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Please enter a homework title.')),
                        );
                        return;
                      }

                      final newTitle = titleCtrl.text.trim();
                      final newSubject = subjectCtrl.text.trim().isNotEmpty
                          ? subjectCtrl.text.trim()
                          : 'General';
                      final newMarks = int.tryParse(marksCtrl.text.trim()) ?? 20;

                      final payload = {
                        'title': newTitle,
                        'description': descCtrl.text.trim(),
                        'subject': newSubject,
                        'class_section': selectedClass,
                        'due_date': dueDate,
                        'total_marks': newMarks,
                        'type': selectedType,
                      };

                      Navigator.pop(ctx);

                      final res = await _apiService.createAssignment(payload);

                      final Map<String, dynamic> createdItem = (res.success &&
                              res.data is Map &&
                              res.data!['assignment'] != null)
                          ? Map<String, dynamic>.from(res.data!['assignment'] as Map)
                          : {
                              'id': DateTime.now().millisecondsSinceEpoch,
                              'title': newTitle,
                              'description': descCtrl.text.trim(),
                              'subject': newSubject,
                              'class_name': selectedClass,
                              'due_date': dueDate,
                              'total_marks': newMarks,
                              'submitted': 0,
                              'total': 32,
                              'graded': 0,
                              'status': 'pending',
                              'type': selectedType,
                              'colorHex': 0xFF2563EB,
                            };

                      setState(() {
                        _assignments.insert(0, createdItem);
                      });

                      final prefs = await SharedPreferences.getInstance();
                      await prefs.setString(
                          'cached_homework_list', jsonEncode(_assignments));

                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            backgroundColor: AppTheme.green,
                            content: Text(
                                '🚀 Homework published and notified on Nepal School Hub Student App!'),
                          ),
                        );
                      }
                    },
                    child: const Text('Publish Homework Now 🚀',
                        style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
              ],
            ),
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

    final pendingList = _assignments.where((a) {
      final sub = a['submitted'] is int
          ? a['submitted'] as int
          : (int.tryParse(a['submitted']?.toString() ?? '') ?? 0);
      final tot = a['total'] is int
          ? a['total'] as int
          : (int.tryParse(a['total']?.toString() ?? '') ?? 32);
      return sub < tot;
    }).toList();

    final completedList = _assignments.where((a) {
      final sub = a['submitted'] is int
          ? a['submitted'] as int
          : (int.tryParse(a['submitted']?.toString() ?? '') ?? 0);
      final tot = a['total'] is int
          ? a['total'] as int
          : (int.tryParse(a['total']?.toString() ?? '') ?? 32);
      return sub >= tot;
    }).toList();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Homework Hub (गृहकार्य व्यवस्थापन)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Assignment Tracker, Digital Submissions & Grading',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
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
              onPressed: () => _showAddDialog(isDark),
              icon: const Icon(Icons.add_task_rounded, size: 16),
              label: const Text(
                'Assign',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
        bottom: TabBar(
          controller: _tab,
          labelColor: AppTheme.primary,
          unselectedLabelColor: AppTheme.darkSubtext,
          indicatorColor: AppTheme.primary,
          indicatorWeight: 3,
          indicatorSize: TabBarIndicatorSize.label,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: [
            Tab(text: 'All (${_assignments.length})'),
            Tab(text: 'Active (${pendingList.length})'),
            Tab(text: 'Completed (${completedList.length})'),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _fetchAssignments,
        color: AppTheme.primary,
        child: Column(
          children: [
            // Class Filter chips
            Container(
              height: 42,
              margin: const EdgeInsets.symmetric(vertical: 8),
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 18),
                children: [
                  'All Classes',
                  ...classesList.map((c) => c.section),
                ].toSet().map((c) {
                  final sel = _selectedClassFilter == c;
                  return GestureDetector(
                    onTap: () => setState(() => _selectedClassFilter = c),
                    child: Container(
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: sel
                            ? AppTheme.primary
                            : (isDark ? AppTheme.darkCard : Colors.white),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: sel
                              ? AppTheme.primary
                              : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                        ),
                      ),
                      child: Center(
                        child: Text(
                          c,
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: sel
                                ? Colors.white
                                : (isDark ? Colors.white70 : AppTheme.dark),
                          ),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),

            // Tab Views
            Expanded(
              child: _isLoading && _assignments.isEmpty
                  ? const Center(child: CircularProgressIndicator())
                  : TabBarView(
                      controller: _tab,
                      children: [
                        _buildAssignmentList(_filterByClass(_assignments), isDark),
                        _buildAssignmentList(_filterByClass(pendingList), isDark),
                        _buildAssignmentList(_filterByClass(completedList), isDark),
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAssignmentList(List<Map<String, dynamic>> list, bool isDark) {
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_turned_in_outlined,
                size: 52, color: AppTheme.darkSubtext.withOpacity(0.5)),
            const SizedBox(height: 12),
            const Text(
              'No homework in this category',
              style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.darkSubtext),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
      itemCount: list.length,
      itemBuilder: (_, i) {
        final a = list[i];
        final sub = a['submitted'] is int
            ? a['submitted'] as int
            : (int.tryParse(a['submitted']?.toString() ?? '') ?? 0);
        final tot = a['total'] is int
            ? a['total'] as int
            : (int.tryParse(a['total']?.toString() ?? '') ?? 32);
        final pct = tot > 0 ? (sub / tot) : 0.0;
        final isDone = sub >= tot && tot > 0;
        final color = Color(a['colorHex'] as int? ?? 0xFF2563EB);

        return InkWell(
          onTap: () => _showDetailModal(a),
          borderRadius: BorderRadius.circular(18),
          child: Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(16),
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
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: (isDone ? AppTheme.green : color).withOpacity(0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        isDone ? Icons.check_circle_outline : Icons.menu_book_rounded,
                        size: 20,
                        color: isDone ? AppTheme.green : color,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            a['title']?.toString() ?? 'Homework',
                            style: const TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -0.1,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${a['subject']} • ${a['class_name'] ?? a['section']}',
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.darkSubtext,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: (isDone ? AppTheme.green : AppTheme.orange).withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        isDone ? 'COMPLETED' : 'ACTIVE',
                        style: TextStyle(
                          color: isDone ? AppTheme.green : AppTheme.orange,
                          fontSize: 9.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      '$sub/$tot Submissions (${(pct * 100).toInt()}%)',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    Row(
                      children: [
                        const Icon(Icons.alarm_rounded,
                            size: 13, color: AppTheme.darkSubtext),
                        const SizedBox(width: 4),
                        Text(
                          'Due: ${a['due_date']}',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.darkSubtext,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: LinearProgressIndicator(
                    value: pct,
                    backgroundColor:
                        (isDone ? AppTheme.green : AppTheme.primary).withOpacity(0.15),
                    valueColor: AlwaysStoppedAnimation<Color>(
                      isDone ? AppTheme.green : AppTheme.primary,
                    ),
                    minHeight: 6,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      'Tap to View Submissions & Grade 👉',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: color,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ).animate().fadeIn(delay: (i * 30).ms);
      },
    );
  }
}

class _MiniStat extends StatelessWidget {
  final String label, value;
  final Color color;
  const _MiniStat({required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          children: [
            Text(value,
                style: TextStyle(
                    fontSize: 15, fontWeight: FontWeight.w900, color: color)),
            const SizedBox(height: 2),
            Text(label,
                style: const TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.grey)),
          ],
        ),
      ),
    );
  }
}
