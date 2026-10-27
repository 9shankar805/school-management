import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../providers/teacher_provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});
  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  int _selectedClass = 0;
  // 0=Present, 1=Absent, 2=Late, 3=Leave
  List<int> _status = [];
  List<String?> _remarks = [];
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();
  bool _isGridView = false;
  String _currentNepaliDate = '१० असोज २०८१ • बिहीबार (Today)';
  String? _lastLoadedClassKey;

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _syncStatusLength(int length) {
    if (_status.length != length) {
      _status = List.filled(length, 0);
      _remarks = List.filled(length, null);
    }
  }

  void _setStatus(int studentIdx, int status) {
    if (studentIdx >= 0 && studentIdx < _status.length) {
      setState(() => _status[studentIdx] = status);
      if (status == 3 && _remarks[studentIdx] == null) {
        _showRemarkDialog(studentIdx);
      }
    }
  }

  void _markAll(int status) {
    setState(() {
      for (int i = 0; i < _status.length; i++) {
        _status[i] = status;
      }
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          status == 0
              ? 'All students marked Present (उपस्थित) ✅'
              : 'All students reset to Absent ❌',
        ),
        duration: const Duration(seconds: 1),
        backgroundColor: status == 0 ? AppTheme.green : AppTheme.red,
      ),
    );
  }

  int get _presentCount => _status.where((s) => s == 0).length;
  int get _absentCount => _status.where((s) => s == 1).length;
  int get _lateCount => _status.where((s) => s == 2).length;
  int get _leaveCount => _status.where((s) => s == 3).length;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final classesList = teacherProvider.teacherClasses;

    if (_selectedClass >= classesList.length && classesList.isNotEmpty) {
      _selectedClass = 0;
    }

    final cls = classesList.isNotEmpty
        ? classesList[_selectedClass]
        : AppData.classes.first;

    // Filter students belonging to this class/section if meta available, or all teacher students
    final allStudents = teacherProvider.studentList;
    final classStudents = allStudents.where((s) {
      if (cls.classId != null && s.classId != null && s.classId != cls.classId) {
        return false;
      }
      if (cls.sectionId != null && s.sectionId != null && s.sectionId != cls.sectionId) {
        return false;
      }
      return true;
    }).toList();

    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;

    final currentKey = '${cls.classId ?? cls.id}_${activeStudents.length}';
    if (_lastLoadedClassKey != currentKey) {
      _lastLoadedClassKey = currentKey;
      _status = List.filled(activeStudents.length, 0);
      _remarks = List.filled(activeStudents.length, null);
    } else if (_status.length != activeStudents.length) {
      _syncStatusLength(activeStudents.length);
    }

    final filteredStudents = activeStudents.asMap().entries.where((entry) {
      if (_searchQuery.isEmpty) return true;
      final name = entry.value.name.toLowerCase();
      final roll = entry.value.rollNo.toLowerCase();
      final q = _searchQuery.toLowerCase();
      return name.contains(q) || roll.contains(q);
    }).toList();

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Haziri Portal (हाजिरी)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Real-Time Student Attendance',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.darkSubtext,
              ),
            ),
          ],
        ),
        leading: Navigator.canPop(context)
            ? CircleIconButton(
                icon: Icons.arrow_back_ios_new_rounded,
                onTap: () => Navigator.pop(context),
                size: 38,
              )
            : null,
        leadingWidth: Navigator.canPop(context) ? 60 : null,
        automaticallyImplyLeading: false,
        actions: [
          // Grid / List View Toggle
          IconButton(
            icon: Icon(
              _isGridView ? Icons.view_list_rounded : Icons.grid_view_rounded,
              size: 20,
              color: AppTheme.primary,
            ),
            tooltip: _isGridView ? 'List View' : 'Grid View',
            onPressed: () => setState(() => _isGridView = !_isGridView),
          ),
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: () => _showSaveDialog(),
              icon: const Icon(Icons.cloud_upload_outlined, size: 16),
              label: const Text(
                'Submit',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
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
          // Class Horizontal Selector Bar
          Container(
            height: 48,
            margin: const EdgeInsets.only(top: 4),
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 18),
              itemCount: classesList.length,
              itemBuilder: (_, i) {
                final sel = i == _selectedClass;
                final c = classesList[i];
                return GestureDetector(
                  onTap: () => setState(() => _selectedClass = i),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    margin: const EdgeInsets.only(right: 8),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      gradient: sel
                          ? const LinearGradient(
                              colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                            )
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
                        width: 1,
                      ),
                      boxShadow: sel
                          ? [
                              BoxShadow(
                                color: AppTheme.primary.withOpacity(0.3),
                                blurRadius: 8,
                                offset: const Offset(0, 3),
                              ),
                            ]
                          : null,
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.class_rounded,
                          size: 14,
                          color: sel ? Colors.white : AppTheme.darkSubtext,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          c.section,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: sel
                                ? Colors.white
                                : (isDark ? Colors.white70 : AppTheme.dark),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 10),

          // Date & Attendance Summary Box
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 18),
            child: Container(
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
                    color: const Color(0xFF0F172A)
                        .withOpacity(isDark ? 0.2 : 0.03),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Date and Class info
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      GestureDetector(
                        onTap: () => _showDatePicker(context, isDark),
                        child: Row(
                          children: [
                            const Icon(Icons.calendar_month_rounded,
                                size: 15, color: AppTheme.primary),
                            const SizedBox(width: 6),
                            Text(
                              _currentNepaliDate,
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.primary,
                              ),
                            ),
                            const SizedBox(width: 4),
                            const Icon(Icons.arrow_drop_down,
                                size: 16, color: AppTheme.primary),
                          ],
                        ),
                      ),
                      Text(
                        'Total: ${cls.students} Students',
                        style: const TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.darkSubtext,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // 4 Status Summary Chips
                  Row(
                    children: [
                      _buildSummaryChip(
                          'Present',
                          '$_presentCount',
                          AppTheme.green,
                          const Color(0xFFECFDF5),
                          isDark),
                      const SizedBox(width: 6),
                      _buildSummaryChip(
                          'Absent',
                          '$_absentCount',
                          AppTheme.red,
                          const Color(0xFFFEF2F2),
                          isDark),
                      const SizedBox(width: 6),
                      _buildSummaryChip(
                          'Late',
                          '$_lateCount',
                          AppTheme.orange,
                          const Color(0xFFFFFBEB),
                          isDark),
                      const SizedBox(width: 6),
                      _buildSummaryChip(
                          'Leave',
                          '$_leaveCount',
                          AppTheme.purple,
                          const Color(0xFFF5F3FF),
                          isDark),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Fast Actions (Mark All Present & Search)
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _markAll(0),
                          icon: const Icon(Icons.done_all_rounded,
                              size: 15, color: AppTheme.green),
                          label: const Text(
                            'Mark All Present (सबै उपस्थित)',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                              color: AppTheme.green,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            side: BorderSide(
                                color: AppTheme.green.withOpacity(0.4)),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        onPressed: () => _markAll(1),
                        icon: const Icon(Icons.refresh_rounded,
                            size: 18, color: AppTheme.darkSubtext),
                        tooltip: 'Reset to Absent',
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Search Field
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 18),
            child: TextField(
              controller: _searchCtrl,
              onChanged: (v) => setState(() => _searchQuery = v),
              decoration: InputDecoration(
                hintText: 'Search student by name or roll number...',
                hintStyle:
                    const TextStyle(fontSize: 12, color: AppTheme.darkSubtext),
                prefixIcon: const Icon(Icons.search_rounded,
                    size: 18, color: AppTheme.darkSubtext),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 16),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                filled: true,
                fillColor: isDark ? AppTheme.darkCard : Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color:
                        isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color:
                        isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 8),

          // Student List or Rapid Grid View
          Expanded(
            child: _isGridView
                ? GridView.builder(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 3,
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                      childAspectRatio: 0.88,
                    ),
                    itemCount: filteredStudents.length,
                    itemBuilder: (context, i) {
                      final entry = filteredStudents[i];
                      final originalIndex = entry.key;
                      final student = entry.value;
                      final status = _status[originalIndex];
                      return _buildGridStudentTile(
                        student: student,
                        index: originalIndex,
                        status: status,
                        isDark: isDark,
                        onTapNext: () {
                          // Cycle through P -> A -> L -> Leave
                          final next = (status + 1) % 4;
                          _setStatus(originalIndex, next);
                        },
                      );
                    },
                  )
                : ListView.builder(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
                    itemCount: filteredStudents.length,
                    itemBuilder: (context, i) {
                      final entry = filteredStudents[i];
                      final originalIndex = entry.key;
                      final student = entry.value;
                      final status = _status[originalIndex];
                      final remark = _remarks[originalIndex];

                      return _buildStudentHaziriTile(
                        student: student,
                        index: originalIndex,
                        status: status,
                        remark: remark,
                        isDark: isDark,
                        onStatusChanged: (newStatus) =>
                            _setStatus(originalIndex, newStatus),
                        onAddRemark: () => _showRemarkDialog(originalIndex),
                      ).animate().fadeIn(delay: (i * 25).ms);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryChip(
      String label, String count, Color color, Color bgLight, bool isDark) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
        decoration: BoxDecoration(
          color: isDark ? color.withOpacity(0.15) : bgLight,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.2), width: 0.8),
        ),
        child: Column(
          children: [
            Text(
              count,
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
            Text(
              label,
              style: TextStyle(
                fontSize: 9.5,
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStudentHaziriTile({
    required Student student,
    required int index,
    required int status,
    required String? remark,
    required bool isDark,
    required ValueChanged<int> onStatusChanged,
    required VoidCallback onAddRemark,
  }) {
    final statusColor = [
      AppTheme.green,
      AppTheme.red,
      AppTheme.orange,
      AppTheme.purple,
    ][status];

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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              // Roll No Badge & Avatar
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.15),
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    student.rollNo,
                    style: TextStyle(
                      color: statusColor,
                      fontWeight: FontWeight.w900,
                      fontSize: 13,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),

              // Student Name & Historic Haziri
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      student.name,
                      style: const TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.1,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Roll #${student.rollNo} • ${(student.attendance * 100).toInt()}% Past Haziri',
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppTheme.darkSubtext,
                      ),
                    ),
                  ],
                ),
              ),

              // 4 Status Buttons: P / A / L / Leave
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _buildHaziriPill('P', AppTheme.green, status == 0,
                      () => onStatusChanged(0)),
                  const SizedBox(width: 4),
                  _buildHaziriPill('A', AppTheme.red, status == 1,
                      () => onStatusChanged(1)),
                  const SizedBox(width: 4),
                  _buildHaziriPill('L', AppTheme.orange, status == 2,
                      () => onStatusChanged(2)),
                  const SizedBox(width: 4),
                  _buildHaziriPill('Leave', AppTheme.purple, status == 3,
                      () => onStatusChanged(3),
                      isWide: true),
                ],
              ),
            ],
          ),
          if (remark != null) ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: AppTheme.purple.withOpacity(0.1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Row(
                children: [
                  const Icon(Icons.note_alt_outlined,
                      size: 11, color: AppTheme.purple),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      'Remark: $remark',
                      style: const TextStyle(
                        fontSize: 10.5,
                        color: AppTheme.purple,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildGridStudentTile({
    required Student student,
    required int index,
    required int status,
    required bool isDark,
    required VoidCallback onTapNext,
  }) {
    final statusColor = [
      AppTheme.green,
      AppTheme.red,
      AppTheme.orange,
      AppTheme.purple,
    ][status];
    final statusText = ['Present', 'Absent', 'Late', 'Leave'][status];

    return GestureDetector(
      onTap: onTapNext,
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: statusColor.withOpacity(0.6),
            width: 1.5,
          ),
          boxShadow: [
            BoxShadow(
              color: statusColor.withOpacity(0.12),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: statusColor.withOpacity(0.15),
                shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(
                  student.rollNo,
                  style: TextStyle(
                    color: statusColor,
                    fontWeight: FontWeight.w900,
                    fontSize: 13,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              student.name.split(' ').first,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
              decoration: BoxDecoration(
                color: statusColor,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                statusText,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 10,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHaziriPill(
    String label,
    Color color,
    bool selected,
    VoidCallback onTap, {
    bool isWide = false,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        width: isWide ? 44 : 28,
        height: 28,
        decoration: BoxDecoration(
          color: selected ? color : color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: selected ? color : color.withOpacity(0.2),
            width: 1,
          ),
          boxShadow: selected
              ? [
                  BoxShadow(
                    color: color.withOpacity(0.35),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontSize: isWide ? 10 : 12,
              fontWeight: FontWeight.w900,
              color: selected ? Colors.white : color,
            ),
          ),
        ),
      ),
    );
  }

  void _showDatePicker(BuildContext context, bool isDark) {
    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select Attendance Date (हाजिरी मिति)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 14),
            ListTile(
              title: const Text('१० असोज २०८१ • बिहीबार (Today)'),
              leading: const Icon(Icons.today, color: AppTheme.primary),
              onTap: () {
                setState(() => _currentNepaliDate = '१० असोज २०८१ • बिहीबार (Today)');
                Navigator.pop(ctx);
              },
            ),
            ListTile(
              title: const Text('०९ असोज २०८१ • बुधबार (Yesterday)'),
              leading: const Icon(Icons.history, color: AppTheme.orange),
              onTap: () {
                setState(() => _currentNepaliDate = '०९ असोज २०८१ • बुधबार (Yesterday)');
                Navigator.pop(ctx);
              },
            ),
            ListTile(
              title: const Text('०८ असोज २०८१ • मंगलबार'),
              leading: const Icon(Icons.event_note, color: AppTheme.darkSubtext),
              onTap: () {
                setState(() => _currentNepaliDate = '०८ असोज २०८१ • मंगलबार');
                Navigator.pop(ctx);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showRemarkDialog(int studentIdx) {
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final cls = classesList.isNotEmpty && _selectedClass < classesList.length
        ? classesList[_selectedClass]
        : AppData.classes.first;
    final allStudents = teacherProvider.studentList;
    final classStudents = allStudents.where((s) {
      if (cls.classId != null && s.classId != null && s.classId != cls.classId) return false;
      if (cls.sectionId != null && s.sectionId != null && s.sectionId != cls.sectionId) return false;
      return true;
    }).toList();
    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;
    final student = studentIdx < activeStudents.length ? activeStudents[studentIdx] : AppData.students.first;
    final ctrl = TextEditingController(text: studentIdx < _remarks.length ? (_remarks[studentIdx] ?? '') : '');

    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Text(
          'Leave Note: ${student.name}',
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
        ),
        content: TextField(
          controller: ctrl,
          decoration: const InputDecoration(
            hintText: 'e.g. Sick application approved, Festival leave',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              if (studentIdx < _remarks.length) {
                setState(() {
                  _remarks[studentIdx] =
                      ctrl.text.trim().isNotEmpty ? ctrl.text.trim() : null;
                });
              }
              Navigator.pop(dialogCtx);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primary,
              foregroundColor: Colors.white,
            ),
            child: const Text('Save Note'),
          ),
        ],
      ),
    );
  }

  void _showSaveDialog() {
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final cls = classesList.isNotEmpty && _selectedClass < classesList.length
        ? classesList[_selectedClass]
        : AppData.classes.first;

    final allStudents = teacherProvider.studentList;
    final classStudents = allStudents.where((s) {
      if (cls.classId != null && s.classId != null && s.classId != cls.classId) return false;
      if (cls.sectionId != null && s.sectionId != null && s.sectionId != cls.sectionId) return false;
      return true;
    }).toList();
    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;

    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppTheme.primary.withOpacity(0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.cloud_upload_rounded,
                  color: AppTheme.primary, size: 22),
            ),
            const SizedBox(width: 10),
            const Text(
              'Submit Haziri 📋',
              style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Class: ${cls.section}',
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14),
            ),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppTheme.lightBg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  Text('🟢 Present: $_presentCount',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 12)),
                  Text('🔴 Absent: $_absentCount',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 12)),
                  Text('🟡 Late: $_lateCount',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 12)),
                ],
              ),
            ),
            const SizedBox(height: 12),
            const Row(
              children: [
                Icon(Icons.sms_outlined, size: 14, color: AppTheme.primary),
                SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Automatic SMS alert will be sent to parents of absent students.',
                    style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: const Text('Cancel',
                style: TextStyle(fontWeight: FontWeight.w700)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(dialogCtx);
              final classId = '${cls.classId ?? cls.id ?? (_selectedClass + 1)}';
              final payload = {
                'date': DateTime.now().toIso8601String().substring(0, 10),
                if (cls.sectionId != null) 'section_id': cls.sectionId,
                'attendance': List.generate(activeStudents.length, (idx) {
                  final s = activeStudents[idx];
                  return {
                    'student_id': s.id ?? (idx + 1),
                    'status': idx < _status.length && _status[idx] == 0
                        ? 'present'
                        : (idx < _status.length && _status[idx] == 1
                            ? 'absent'
                            : (idx < _status.length && _status[idx] == 2 ? 'late' : 'excused')),
                    'remarks': idx < _remarks.length && _remarks[idx] != null ? _remarks[idx]! : '',
                  };
                }),
              };

              final success =
                  await Provider.of<TeacherProvider>(context, listen: false)
                      .submitAttendance(classId, payload);

              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    backgroundColor: AppTheme.green,
                    content: Text(
                      success
                          ? 'Haziri submitted and synchronized with Nepal School Hub MIS!'
                          : 'Haziri saved locally (Offline sync active).',
                    ),
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Confirm & Save'),
          ),
        ],
      ),
    );
  }
}
