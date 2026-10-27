import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class AssignmentsScreen extends StatefulWidget {
  const AssignmentsScreen({super.key});
  @override
  State<AssignmentsScreen> createState() => _AssignmentsScreenState();
}

class _AssignmentsScreenState extends State<AssignmentsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tab;
  String _selectedClassFilter = 'All Classes';

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  List<TeacherAssignment> _filterByClass(List<TeacherAssignment> list) {
    if (_selectedClassFilter == 'All Classes') return list;
    return list.where((a) => a.classSection == _selectedClassFilter).toList();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final assignmentsList = teacherProvider.assignmentList;
    final classesList = teacherProvider.teacherClasses;

    final pendingList = assignmentsList.where((a) => a.submitted < a.total).toList();
    final completedList = assignmentsList.where((a) => a.submitted == a.total).toList();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Homework Hub (गृहकार्य)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Digital Assignment Tracker & Submissions',
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
            Tab(text: 'All (${assignmentsList.length})'),
            Tab(text: 'Active (${pendingList.length})'),
            Tab(text: 'Graded (${completedList.length})'),
          ],
        ),
      ),
      body: Column(
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
              ].map((c) {
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

          // Tab views
          Expanded(
            child: TabBarView(
              controller: _tab,
              children: [
                _buildAssignmentList(_filterByClass(assignmentsList), isDark),
                _buildAssignmentList(_filterByClass(pendingList), isDark),
                _buildAssignmentList(_filterByClass(completedList), isDark),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAssignmentList(List<TeacherAssignment> assignments, bool isDark) {
    if (assignments.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_turned_in_outlined,
                size: 52, color: AppTheme.darkSubtext.withOpacity(0.5)),
            const SizedBox(height: 12),
            const Text(
              'No homework in this category',
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
      itemCount: assignments.length,
      itemBuilder: (_, i) {
        final a = assignments[i];
        final pct = a.submitted / a.total;
        final isDone = a.submitted == a.total;
        final color = Color(a.colorHex);

        return Container(
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
                          a.title,
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
                          '${a.subject} • ${a.classSection}',
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
                      isDone ? 'COMPLETED' : 'PENDING',
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
                    '${a.submitted}/${a.total} Submissions (${(pct * 100).toInt()}%)',
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Row(
                    children: [
                      const Icon(Icons.alarm_rounded, size: 13, color: AppTheme.darkSubtext),
                      const SizedBox(width: 4),
                      Text(
                        'Due: ${a.dueDate}',
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
                  backgroundColor: (isDone ? AppTheme.green : AppTheme.primary).withOpacity(0.15),
                  valueColor: AlwaysStoppedAnimation<Color>(
                    isDone ? AppTheme.green : AppTheme.primary,
                  ),
                  minHeight: 6,
                ),
              ),
            ],
          ),
        ).animate().fadeIn(delay: (i * 30).ms);
      },
    );
  }

  void _showAddDialog(bool isDark) {
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final titleCtrl = TextEditingController();
    final subjectCtrl = TextEditingController(text: 'Compulsory Mathematics');
    String selectedClass = classesList.isNotEmpty ? classesList.first.section : 'Grade 10 - A';
    String dueDate = '१२ असोज २०८१ (Friday)';

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
                Icon(Icons.add_task_rounded, color: AppTheme.primary, size: 22),
                SizedBox(width: 8),
                Text(
                  'Assign Homework (नयाँ गृहकार्य)',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextField(
              controller: titleCtrl,
              decoration: InputDecoration(
                labelText: 'Topic / Exercise Title',
                hintText: 'e.g. Chapter 4 Trigonometry Problem Set',
                filled: true,
                fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
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
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                    ),
                    items: classesList
                        .map((c) => DropdownMenuItem(value: c.section, child: Text(c.section)))
                        .toList(),
                    onChanged: (v) => selectedClass = v!,
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
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: () {
                  if (titleCtrl.text.trim().isNotEmpty) {
                    setState(() {
                      _assignments.insert(
                        0,
                        TeacherAssignment(
                          title: titleCtrl.text.trim(),
                          subject: subjectCtrl.text.trim().isNotEmpty ? subjectCtrl.text.trim() : 'General',
                          dueDate: dueDate,
                          classSection: selectedClass,
                          submitted: 0,
                          total: 32,
                          colorHex: 0xFF0F4C81,
                        ),
                      );
                    });
                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        backgroundColor: AppTheme.green,
                        content: Text('Homework assigned and notified on Nepal School Hub Student App ✅'),
                      ),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: const Text('Publish Homework Now 🚀', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
