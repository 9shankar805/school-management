import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class LessonPlannerScreen extends StatefulWidget {
  const LessonPlannerScreen({super.key});
  @override
  State<LessonPlannerScreen> createState() => _LessonPlannerScreenState();
}

class _LessonPlannerScreenState extends State<LessonPlannerScreen> {
  int _selectedDay = 4; // Thursday (Today)

  final List<Map<String, String>> _days = [
    {'label': 'Sun (आइत)', 'num': '०६'},
    {'label': 'Mon (सोम)', 'num': '०७'},
    {'label': 'Tue (मंगल)', 'num': '०८'},
    {'label': 'Wed (बुध)', 'num': '०९'},
    {'label': 'Thu (बिही)', 'num': '१०'},
    {'label': 'Fri (शुक्र)', 'num': '११'},
  ];

  final List<Map<String, dynamic>> _plans = [
    {
      'class': 'Compulsory Mathematics',
      'section': 'Grade 10 - A (Mt. Everest)',
      'time': '08:30 - 09:30 AM (Period 1)',
      'colorHex': 0xFF0F4C81,
      'objective': 'Students will solve quadratic equations by completing the square and quadratic formula.',
      'materials': ['CDC Nepal Math Book Ch.5', 'Smart Interactive Board', 'Casio fx-991ES Calculator'],
      'activities': [
        {'name': 'Mental Math Warmup: Quick quadratic roots', 'duration': '5 min'},
        {'name': 'Derivation of Quadratic Formula (-b ± √(b²-4ac))/2a', 'duration': '15 min'},
        {'name': 'Step-by-step example problem solving on board', 'duration': '15 min'},
        {'name': 'Independent student practice with peer-check', 'duration': '20 min'},
        {'name': 'Exit Ticket / Quick concept check', 'duration': '5 min'},
      ],
      'homework': 'CDC Textbook Page 142, Q.No 4 (a to f)',
      'status': 'ready',
    },
    {
      'class': 'Optional Mathematics',
      'section': 'Grade 10 - B (Annapurna)',
      'time': '10:00 - 11:00 AM (Period 2)',
      'colorHex': 0xFF2563EB,
      'objective': 'Students will prove compound trigonometric multiple angle identities sin 2A & cos 2A.',
      'materials': ['Trigonometry Formula Chart', 'Graph Notebook'],
      'activities': [
        {'name': 'Review: Addition formula sin(A+B)', 'duration': '10 min'},
        {'name': 'Geometric proof of multiple angle formulas', 'duration': '20 min'},
        {'name': 'Group discussion and problem solving', 'duration': '25 min'},
        {'name': 'Summary & doubt clearance', 'duration': '5 min'},
      ],
      'homework': 'Optional Math Practice Book Chapter 3 Set B',
      'status': 'ready',
    },
    {
      'class': 'Advanced Calculus',
      'section': 'Grade 12 - Science A',
      'time': '01:00 - 02:00 PM (Period 5)',
      'colorHex': 0xFF10B981,
      'objective': 'Introduce definite integrals and fundamental theorem of calculus.',
      'materials': ['NEB Grade 12 Calculus Reference', 'Graphing App GeoGebra'],
      'activities': [
        {'name': 'Riemann sums area approximation concept', 'duration': '15 min'},
        {'name': 'Fundamental theorem integration demo', 'duration': '20 min'},
        {'name': 'Board workout of challenging past questions', 'duration': '25 min'},
      ],
      'homework': 'Exercises 8.4, Problems 1 to 8',
      'status': 'draft',
    },
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacher = Provider.of<TeacherProvider>(context);

    List<Map<String, dynamic>> activePlans = _plans;
    if (teacher.teacherClasses.isNotEmpty) {
      activePlans = teacher.teacherClasses.asMap().entries.map((entry) {
        final i = entry.key;
        final c = entry.value;
        final existing = i < _plans.length ? _plans[i] : _plans.first;
        return {
          ...existing,
          'class': c.subject.isNotEmpty ? c.subject : 'Subject Lecture',
          'section': '${c.name} (Room ${c.room})',
          'time': '${c.time} (Period ${i + 1})',
        };
      }).toList();
    }

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Lesson Planner (पाठ योजना)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Curriculum & Activity Timeline • 2081',
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
              onPressed: () => _showAddPlanSheet(isDark),
              icon: const Icon(Icons.note_add_rounded, size: 16),
              label: const Text(
                'New Plan',
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
      ),
      body: Column(
        children: [
          // 6-Day School Week Selector (Sun - Fri)
          Container(
            height: 72,
            margin: const EdgeInsets.fromLTRB(16, 6, 16, 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: List.generate(_days.length, (i) {
                final isSelected = i == _selectedDay;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _selectedDay = i),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      margin: const EdgeInsets.symmetric(horizontal: 3),
                      decoration: BoxDecoration(
                        gradient: isSelected
                            ? const LinearGradient(
                                colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                              )
                            : null,
                        color: isSelected
                            ? null
                            : (isDark ? AppTheme.darkCard : Colors.white),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isSelected
                              ? AppTheme.primary
                              : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                        ),
                        boxShadow: isSelected
                            ? [
                                BoxShadow(
                                  color: AppTheme.primary.withOpacity(0.35),
                                  blurRadius: 8,
                                  offset: const Offset(0, 3),
                                ),
                              ]
                            : null,
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            _days[i]['label']!,
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: isSelected ? Colors.white : AppTheme.darkSubtext,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _days[i]['num']!,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: isSelected ? Colors.white : (isDark ? Colors.white : AppTheme.dark),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
            ),
          ),

          // Plans List
          Expanded(
            child: ListView.builder(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(18, 0, 18, 90),
              itemCount: activePlans.length,
              itemBuilder: (_, i) => _buildLessonCard(activePlans[i], isDark, i),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLessonCard(Map<String, dynamic> plan, bool isDark, int index) {
    final isDraft = plan['status'] == 'draft';
    final activities = plan['activities'] as List;
    final materials = plan['materials'] as List;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(20),
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
          // Header Row
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppTheme.primary.withOpacity(isDark ? 0.15 : 0.05),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(19)),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.menu_book_rounded, color: AppTheme.primary, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        plan['class'] as String,
                        style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900),
                      ),
                      Text(
                        '${plan['section']} • ${plan['time']}',
                        style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: (isDraft ? AppTheme.orange : AppTheme.green).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    isDraft ? 'DRAFT' : 'READY',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                      color: isDraft ? AppTheme.orange : AppTheme.green,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Objective & Details
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Learning Objective (सिकाइ उपलब्धि):',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
                ),
                const SizedBox(height: 3),
                Text(
                  plan['objective'] as String,
                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, height: 1.4),
                ),
                const SizedBox(height: 10),

                // Activities list
                ...activities.take(3).map((a) => Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        children: [
                          const Icon(Icons.check_circle_outline_rounded, size: 14, color: AppTheme.primary),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              '${a['name']} (${a['duration']})',
                              style: const TextStyle(fontSize: 11.5, color: AppTheme.darkSubtext),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    )),
                const SizedBox(height: 8),

                // Homework tag
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.assignment_outlined, size: 14, color: AppTheme.orange),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Homework: ${plan['homework']}',
                          style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    ).animate().fadeIn(delay: (index * 40).ms);
  }

  void _showAddPlanSheet(bool isDark) {
    final titleCtrl = TextEditingController();
    final objCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(
          20,
          20,
          20,
          MediaQuery.of(ctx).viewInsets.bottom + 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.note_add_rounded, color: AppTheme.primary, size: 22),
                SizedBox(width: 8),
                Text(
                  'Create Lesson Plan (नयाँ पाठ योजना)',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextField(
              controller: titleCtrl,
              decoration: InputDecoration(
                labelText: 'Topic / Chapter Title',
                hintText: 'e.g. Linear Programming & Simplex',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: objCtrl,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Learning Objectives & Competencies',
                hintText: 'Students will learn to formulate constraints...',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: () {
                  if (titleCtrl.text.trim().isNotEmpty) {
                    setState(() {
                      _plans.insert(0, {
                        'class': titleCtrl.text.trim(),
                        'section': 'Grade 10 - A',
                        'time': '11:30 - 12:30 PM',
                        'colorHex': 0xFF0F4C81,
                        'objective': objCtrl.text.trim().isNotEmpty
                            ? objCtrl.text.trim()
                            : 'Students will master key textbook concepts.',
                        'materials': ['Textbook', 'Interactive board'],
                        'activities': [
                          {'name': 'Introduction & concept review', 'duration': '15 min'},
                          {'name': 'Practice examples', 'duration': '30 min'},
                        ],
                        'homework': 'Practice worksheet Q1-5',
                        'status': 'ready',
                      });
                    });
                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        backgroundColor: AppTheme.green,
                        content: Text('Lesson plan saved to Teacher Diary ✅'),
                      ),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: const Text('Save Lesson Plan', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
