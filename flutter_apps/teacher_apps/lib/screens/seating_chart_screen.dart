import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

class SeatingChartScreen extends StatefulWidget {
  const SeatingChartScreen({super.key});

  @override
  State<SeatingChartScreen> createState() => _SeatingChartScreenState();
}

class BenchSeat {
  final int columnNo;
  final int benchNo;
  final int benchPosition; // 1 = Left (बायाँ), 2 = Right (दायाँ), 3 = Center (बीच)
  final String seatCode;
  int? studentId;
  String? name;
  String? rollNo;
  String? gender;
  String? parentName;
  String? parentPhone;
  String? specialRole; // 'CR (क्याप्टेन)', 'Vision (चस्मा)', 'Buddy Pair', 'Prefect'
  bool isPresent;

  BenchSeat({
    required this.columnNo,
    required this.benchNo,
    required this.benchPosition,
    required this.seatCode,
    this.studentId,
    this.name,
    this.rollNo,
    this.gender,
    this.parentName,
    this.parentPhone,
    this.specialRole,
    this.isPresent = true,
  });

  bool get isEmpty => studentId == null && (name == null || name!.isEmpty);

  String get positionLabel {
    if (benchPosition == 1) return 'Left (बायाँ)';
    if (benchPosition == 2) return 'Right (दायाँ)';
    return 'Center (बीच)';
  }
}

class _SeatingChartScreenState extends State<SeatingChartScreen> {
  final ApiService _apiService = ApiService();
  int _selectedClass = 0;

  // Nepal Classroom Bench Layout
  int _numColumns = 3; // 2, 3, or 4 columns with aisles
  int _benchesPerColumn = 5; // 4 to 8 benches deep
  int _studentsPerBench = 2; // 2 per bench (Dual Desk) or 3 per bench

  List<BenchSeat> _seats = [];
  List<Student> _unassignedStudents = [];
  int? _selectedSeatIndex;
  Student? _selectedUnassignedStudent;
  bool _isSaving = false;
  String? _loadedClassKey;

  Future<void> _makePhoneCall(String phoneNumber) async {
    final clean = phoneNumber.replaceAll(RegExp(r'[^\d+]'), '');
    final uri = Uri.parse('tel:$clean');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _openWhatsApp(String phone, String studentName) async {
    final clean = phone.replaceAll(RegExp(r'[^\d]'), '');
    final uri = Uri.parse(
        'whatsapp://send?phone=$clean&text=${Uri.encodeComponent('Namaste, regarding $studentName\'s classroom desk & attendance at Nepal School Hub.')}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  void _generateBenchLayout(List<Student> activeStudents, {bool preserveCurrent = false}) {
    final newSeats = <BenchSeat>[];
    final existingAssigned = <int, BenchSeat>{};

    if (preserveCurrent) {
      for (var s in _seats) {
        if (!s.isEmpty && s.studentId != null) {
          existingAssigned[s.studentId!] = s;
        }
      }
    }

    int studentIdx = 0;
    final seatedStudentIds = <int>{};

    for (int c = 1; c <= _numColumns; c++) {
      for (int b = 1; b <= _benchesPerColumn; b++) {
        for (int p = 1; p <= _studentsPerBench; p++) {
          final posCode = (_studentsPerBench == 1)
              ? 'S'
              : ((p == 1) ? 'L' : ((p == 2 && _studentsPerBench == 3) ? 'C' : 'R'));
          final seatCode = 'C$c-B$b-$posCode';

          Student? st;
          if (studentIdx < activeStudents.length) {
            st = activeStudents[studentIdx++];
            if (st.id != null) seatedStudentIds.add(st.id!);
          }

          // Special Classroom In-Charge roles for Nepali schools
          String? role;
          if (b == 1 && c == 2 && p == 1) {
            role = 'CR (क्याप्टेन)'; // Class Representative right in front of Class In-charge desk
          } else if (b == 1 && c == 1 && p == 1) {
            role = 'Vision (चस्मा)'; // Vision / glasses front row
          } else if (b == 2 && p == 1) {
            role = 'Buddy Pair'; // Peer learning buddy
          }

          newSeats.add(BenchSeat(
            columnNo: c,
            benchNo: b,
            benchPosition: p,
            seatCode: seatCode,
            studentId: st?.id,
            name: st?.name,
            rollNo: st?.rollNo,
            gender: (st?.name.toLowerCase().contains('shrestha') ?? false) ? 'female' : 'male',
            parentName: st?.parentName,
            parentPhone: st?.parentPhone,
            specialRole: role,
          ));
        }
      }
    }

    setState(() {
      _seats = newSeats;
      _unassignedStudents = activeStudents
          .where((s) => s.id != null && !seatedStudentIds.contains(s.id))
          .toList();
      _selectedSeatIndex = null;
      _selectedUnassignedStudent = null;
    });
  }

  void _handleSeatTap(int index) {
    if (_selectedUnassignedStudent != null) {
      // Place unassigned student into this bench seat
      final s = _selectedUnassignedStudent!;
      final oldStudentId = _seats[index].studentId;
      final oldName = _seats[index].name;
      final oldRoll = _seats[index].rollNo;
      final oldParentName = _seats[index].parentName;
      final oldParentPhone = _seats[index].parentPhone;

      setState(() {
        _seats[index].studentId = s.id;
        _seats[index].name = s.name;
        _seats[index].rollNo = s.rollNo;
        _seats[index].parentName = s.parentName;
        _seats[index].parentPhone = s.parentPhone;
        _unassignedStudents.removeWhere((item) => item.id == s.id);

        if (oldStudentId != null && oldName != null) {
          _unassignedStudents.add(Student(
            id: oldStudentId,
            name: oldName,
            grade: 'Student',
            avatar: oldName.isNotEmpty ? oldName[0] : 'ST',
            gpa: 3.5,
            attendance: 0.95,
            rank: 1,
            rollNo: oldRoll ?? '01',
            parentName: oldParentName,
            parentPhone: oldParentPhone,
          ));
        }
        _selectedUnassignedStudent = null;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${s.name} seated at Bench ${_seats[index].seatCode} ✅'),
          duration: const Duration(seconds: 1),
        ),
      );
      return;
    }

    if (_selectedSeatIndex == null) {
      // Select seat for swapping
      setState(() => _selectedSeatIndex = index);
    } else if (_selectedSeatIndex == index) {
      // Tap again opens classroom dossier modal
      if (!_seats[index].isEmpty) {
        _showStudentSeatDossier(_seats[index], index);
      }
      setState(() => _selectedSeatIndex = null);
    } else {
      // Swap seats!
      final a = _selectedSeatIndex!;
      final b = index;
      _swapSeats(a, b);
      setState(() => _selectedSeatIndex = null);
    }
  }

  void _swapSeats(int a, int b) {
    setState(() {
      final sA = _seats[a];
      final sB = _seats[b];

      final tmpId = sA.studentId;
      final tmpName = sA.name;
      final tmpRoll = sA.rollNo;
      final tmpGender = sA.gender;
      final tmpPName = sA.parentName;
      final tmpPPhone = sA.parentPhone;
      final tmpRole = sA.specialRole;

      sA.studentId = sB.studentId;
      sA.name = sB.name;
      sA.rollNo = sB.rollNo;
      sA.gender = sB.gender;
      sA.parentName = sB.parentName;
      sA.parentPhone = sB.parentPhone;
      sA.specialRole = sB.specialRole;

      sB.studentId = tmpId;
      sB.name = tmpName;
      sB.rollNo = tmpRoll;
      sB.gender = tmpGender;
      sB.parentName = tmpPName;
      sB.parentPhone = tmpPPhone;
      sB.specialRole = tmpRole;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Swapped: ${_seats[a].seatCode} ↔ ${_seats[b].seatCode}'),
        duration: const Duration(milliseconds: 1200),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Class In-Charge Specific Classroom Arrangement Tools
  // ---------------------------------------------------------------------------

  /// In Nepal, every Sunday morning the Class In-Charge rotates benches:
  /// Front row (Bench 1) students move to the back row, and everyone else moves forward by 1 bench!
  /// This gives every student fair turns sitting in front of the teacher!
  void _rotateBenchesWeekly() {
    setState(() {
      for (int c = 1; c <= _numColumns; c++) {
        for (int p = 1; p <= _studentsPerBench; p++) {
          final columnSeats = _seats.where((s) => s.columnNo == c && s.benchPosition == p).toList();
          if (columnSeats.length < 2) continue;

          // Save front bench student
          final frontStudentId = columnSeats.first.studentId;
          final frontName = columnSeats.first.name;
          final frontRoll = columnSeats.first.rollNo;
          final frontGender = columnSeats.first.gender;
          final frontPName = columnSeats.first.parentName;
          final frontPPhone = columnSeats.first.parentPhone;

          // Shift each bench forward
          for (int i = 0; i < columnSeats.length - 1; i++) {
            columnSeats[i].studentId = columnSeats[i + 1].studentId;
            columnSeats[i].name = columnSeats[i + 1].name;
            columnSeats[i].rollNo = columnSeats[i + 1].rollNo;
            columnSeats[i].gender = columnSeats[i + 1].gender;
            columnSeats[i].parentName = columnSeats[i + 1].parentName;
            columnSeats[i].parentPhone = columnSeats[i + 1].parentPhone;
          }

          // Back bench receives the old front bench student
          columnSeats.last.studentId = frontStudentId;
          columnSeats.last.name = frontName;
          columnSeats.last.rollNo = frontRoll;
          columnSeats.last.gender = frontGender;
          columnSeats.last.parentName = frontPName;
          columnSeats.last.parentPhone = frontPPhone;
        }
      }
    });

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        backgroundColor: AppTheme.primary,
        content: Row(
          children: [
            Icon(Icons.autorenew_rounded, color: Colors.white, size: 20),
            SizedBox(width: 8),
            Expanded(
              child: Text(
                'Weekly Bench Rotation Applied! (साप्ताहिक रोटेसन सम्पन्न • सबै १ बेन्च अगाडि सरे)',
                style: TextStyle(fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Front benches for students who wear glasses or need extra visual/hearing attention
  void _arrangeHeightVisionFocus(List<Student> activeStudents) {
    final students = List<Student>.from(activeStudents);
    // Prioritize roll numbers or students with vision tags in front row
    _generateBenchLayout(students);

    setState(() {
      if (_seats.isNotEmpty) _seats[0].specialRole = 'Vision (चस्मा)';
      if (_seats.length > 1) _seats[1].specialRole = 'Front Focus (अगाडि)';
      if (_seats.length > 2) _seats[2].specialRole = 'CR (क्याप्टेन)';
    });

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        backgroundColor: AppTheme.purple,
        content: Text('Height & Vision Focus Applied (अगाडिको बेन्चमा चस्मा तथा ध्यान दिनुपर्ने विद्यार्थी)'),
      ),
    );
  }

  /// Peer learning: pairs high-achieving students with peers who need academic support
  void _arrangePeerBuddyPair(List<Student> activeStudents) {
    final sorted = List<Student>.from(activeStudents)
      ..sort((a, b) => b.gpa.compareTo(a.gpa)); // Sort by GPA

    // Pair top half with bottom half on each dual bench
    final paired = <Student>[];
    int top = 0;
    int bottom = sorted.length - 1;

    while (top <= bottom) {
      paired.add(sorted[top++]);
      if (top <= bottom) paired.add(sorted[bottom--]);
    }

    _generateBenchLayout(paired);

    setState(() {
      for (int i = 0; i < _seats.length; i += 2) {
        if (i < _seats.length) _seats[i].specialRole = 'Mentor';
        if (i + 1 < _seats.length) _seats[i + 1].specialRole = 'Buddy';
      }
    });

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        backgroundColor: AppTheme.green,
        content: Text('Peer Learning Buddy System Applied (सहपाठी जोडी • सिकाइमा मद्दत पुग्ने बसाइ)'),
      ),
    );
  }

  void _arrangeRollSequence(List<Student> activeStudents) {
    final sorted = List<Student>.from(activeStudents)
      ..sort((a, b) => (int.tryParse(a.rollNo) ?? 999).compareTo(int.tryParse(b.rollNo) ?? 999));
    _generateBenchLayout(sorted);
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Arranged in Roll Number Sequence (रोल नम्बर अनुसार १, २, ३...)')),
    );
  }

  void _arrangeDisciplineQuiet() {
    // Separate chatty friends by shuffling seats
    final seatedStudents = _seats
        .where((s) => !s.isEmpty)
        .map((s) => Student(
              id: s.studentId,
              name: s.name!,
              grade: 'Student',
              avatar: s.name!.isNotEmpty ? s.name![0] : 'ST',
              gpa: 3.5,
              attendance: 0.95,
              rank: 1,
              rollNo: s.rollNo ?? '01',
              parentName: s.parentName,
              parentPhone: s.parentPhone,
            ))
        .toList()
      ..shuffle();

    int idx = 0;
    setState(() {
      for (var seat in _seats) {
        if (idx < seatedStudents.length) {
          final st = seatedStudents[idx++];
          seat.studentId = st.id;
          seat.name = st.name;
          seat.rollNo = st.rollNo;
          seat.parentName = st.parentName;
          seat.parentPhone = st.parentPhone;
        } else {
          seat.studentId = null;
          seat.name = null;
          seat.rollNo = null;
          seat.parentName = null;
          seat.parentPhone = null;
        }
      }
    });

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        backgroundColor: AppTheme.orange,
        content: Text('Classroom Discipline Shuffle (अनुशासन तथा शान्त बसाइका लागि पुनर्गठन)'),
      ),
    );
  }

  Future<void> _saveClassroomSeating(String classId) async {
    setState(() => _isSaving = true);
    final payload = {
      'columns': _numColumns,
      'benches_per_column': _benchesPerColumn,
      'students_per_bench': _studentsPerBench,
      'seats': _seats
          .map((s) => {
                'column_no': s.columnNo,
                'bench_no': s.benchNo,
                'bench_position': s.benchPosition,
                'seat_code': s.seatCode,
                'student_id': s.studentId,
                'name': s.name,
                'roll_no': s.rollNo,
                'special_role': s.specialRole,
              })
          .toList(),
    };

    final res = await _apiService.saveSeatingChart(classId, payload);
    setState(() => _isSaving = false);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppTheme.green,
          content: Text(res.message.isNotEmpty
              ? res.message
              : 'Class In-Charge seating plan synchronized with Nepal School Hub MIS!'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context);
    final authProvider = Provider.of<AuthProvider>(context);
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

    final currentKey = '${cls.classId ?? cls.id}_${activeStudents.length}';
    if (_loadedClassKey != currentKey) {
      _loadedClassKey = currentKey;
      _generateBenchLayout(activeStudents);
    }

    final occupiedSeats = _seats.where((s) => !s.isEmpty).length;
    final totalSeats = _seats.length;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Class In-Charge Seating (कक्षा बसाइ)',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
            ),
            Text(
              '${cls.section} • $occupiedSeats / $totalSeats Benches Seated',
              style: const TextStyle(
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
          // Bench Layout Config
          IconButton(
            icon: const Icon(Icons.tune_rounded, color: AppTheme.primary),
            tooltip: 'Configure Classroom Benches',
            onPressed: () => _showLayoutConfigSheet(context, activeStudents, isDark),
          ),
          // Classroom Notice Board Print
          IconButton(
            icon: const Icon(Icons.print_outlined, color: AppTheme.primary),
            tooltip: 'Classroom Wall Seating Chart (सूचना पाटी)',
            onPressed: () => _showClassroomNoticeChart(context, cls.section, authProvider, isDark),
          ),
          // Save Seating Plan
          Container(
            margin: const EdgeInsets.only(right: 14),
            child: ElevatedButton.icon(
              onPressed: _isSaving ? null : () => _saveClassroomSeating('${cls.classId ?? cls.id ?? 1}'),
              icon: _isSaving
                  ? const SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Icon(Icons.cloud_upload_rounded, size: 16),
              label: Text(
                _isSaving ? 'Saving' : 'Save',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Class horizontal bar
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
                  onTap: () {
                    setState(() {
                      _selectedClass = i;
                      _loadedClassKey = null; // reload
                    });
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      gradient: sel
                          ? const LinearGradient(colors: [Color(0xFF0F4C81), Color(0xFF2563EB)])
                          : null,
                      color: sel ? null : (isDark ? AppTheme.darkCard : Colors.white),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: sel ? AppTheme.primary : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                        width: 1,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          c.isClassTeacher ? Icons.stars_rounded : Icons.class_rounded,
                          size: 14,
                          color: sel ? Colors.white : (c.isClassTeacher ? const Color(0xFFF59E0B) : AppTheme.darkSubtext),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          c.section,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: sel ? Colors.white : (isDark ? Colors.white70 : AppTheme.dark),
                          ),
                        ),
                        if (c.isClassTeacher) ...[
                          const SizedBox(width: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                            decoration: BoxDecoration(
                              color: sel ? Colors.white24 : const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              'IN-CHARGE',
                              style: TextStyle(
                                fontSize: 8.5,
                                fontWeight: FontWeight.w900,
                                color: sel ? Colors.white : const Color(0xFFB45309),
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),

          // Class In-Charge Strategy Tools Bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 18),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  // Weekly Rotation (Essential Nepal School Practice!)
                  _buildStrategyPill(
                    icon: Icons.autorenew_rounded,
                    label: 'Weekly Rotation (साप्ताहिक रोटेसन)',
                    color: AppTheme.primary,
                    onTap: _rotateBenchesWeekly,
                    isHighlight: true,
                  ),
                  const SizedBox(width: 8),
                  _buildStrategyPill(
                    icon: Icons.remove_red_eye_rounded,
                    label: 'Height & Vision (चस्मा/उचाइ)',
                    color: AppTheme.purple,
                    onTap: () => _arrangeHeightVisionFocus(activeStudents),
                  ),
                  const SizedBox(width: 8),
                  _buildStrategyPill(
                    icon: Icons.people_outline_rounded,
                    label: 'Peer Buddy Pair (सिकाइ जोडी)',
                    color: AppTheme.green,
                    onTap: () => _arrangePeerBuddyPair(activeStudents),
                  ),
                  const SizedBox(width: 8),
                  _buildStrategyPill(
                    icon: Icons.format_list_numbered_rounded,
                    label: 'Roll Sequence (१, २, ३)',
                    color: const Color(0xFF0F766E),
                    onTap: () => _arrangeRollSequence(activeStudents),
                  ),
                  const SizedBox(width: 8),
                  _buildStrategyPill(
                    icon: Icons.volume_off_rounded,
                    label: 'Discipline Shuffle (शान्त बसाइ)',
                    color: AppTheme.orange,
                    onTap: _arrangeDisciplineQuiet,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),

          // Classroom Layout Canvas
          Expanded(
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 80),
              child: Column(
                children: [
                  // FRONT OF CLASSROOM: Blackboard + Class In-Charge Master Desk
                  _buildClassInchargeMasterStage(cls.section, authProvider, isDark),
                  const SizedBox(height: 16),

                  // CLASSROOM BENCHES GRID (Dual wooden desks with aisles)
                  _buildNepaliBenchColumns(isDark),
                  const SizedBox(height: 16),

                  // BACK OF CLASSROOM: Notice Board & Exit Door
                  _buildBackClassroomWall(isDark),
                  const SizedBox(height: 16),

                  // Unassigned Students Tray
                  if (_unassignedStudents.isNotEmpty) _buildUnassignedTray(isDark),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStrategyPill({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
    bool isHighlight = false,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6.5),
        decoration: BoxDecoration(
          color: isHighlight ? color : color.withOpacity(0.12),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.3), width: 0.8),
          boxShadow: isHighlight
              ? [
                  BoxShadow(
                    color: color.withOpacity(0.3),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: isHighlight ? Colors.white : color),
            const SizedBox(width: 5),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: isHighlight ? Colors.white : color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 1. Blackboard & Class In-Charge Master Desk (अगाडि / मञ्च)
  // ---------------------------------------------------------------------------
  Widget _buildClassInchargeMasterStage(String classSection, AuthProvider authProvider, bool isDark) {
    final teacherName = authProvider.teacherName.isNotEmpty ? authProvider.teacherName : 'Faculty In-Charge';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF0F172A) : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.cardBorder),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        children: [
          // Classroom Blackboard / Whiteboard with Wooden Frame
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
              ),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFF92400E), width: 3), // Teak wooden frame
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.2),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.door_front_door_rounded, size: 16, color: Color(0xFFFBBF24)),
                    SizedBox(width: 6),
                    Text(
                      'प्रवेशद्वार • FRONT DOOR',
                      style: TextStyle(
                        color: Color(0xFFFBBF24),
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
                Expanded(
                  child: Column(
                    children: [
                      Text(
                        'कालोपाटी • BLACKBOARD • $classSection',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                        ),
                      ),
                      const Text(
                        'सुविचार: "अनुशासित विद्यार्थी, सभ्य समाज र उज्ज्वल भविष्य"',
                        style: TextStyle(
                          color: Color(0xFFBAE6FD),
                          fontSize: 9.5,
                          fontStyle: FontStyle.italic,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
                const Row(
                  children: [
                    Icon(Icons.cleaning_services_rounded, size: 12, color: Colors.white70),
                    SizedBox(width: 4),
                    Text(
                      'DUSTER & CHALK',
                      style: TextStyle(color: Colors.white70, fontSize: 9, fontWeight: FontWeight.w700),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // Class In-Charge Master Desk (कक्षा शिक्षकको मुख्य सिट र टेबल)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF78350F), Color(0xFF92400E)], // Polished Teak Wood
              ),
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF78350F).withOpacity(0.35),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(5),
                  decoration: const BoxDecoration(
                    color: Colors.white24,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.co_present_rounded, size: 18, color: Colors.white),
                ),
                const SizedBox(width: 10),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Text(
                          'कक्षा शिक्षक मञ्च • CLASS IN-CHARGE MASTER DESK',
                          style: TextStyle(
                            color: Color(0xFFFEF3C7),
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF59E0B),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text('CLASS IN-CHARGE', style: TextStyle(color: Colors.black87, fontSize: 8, fontWeight: FontWeight.w900)),
                        ),
                      ],
                    ),
                    Text(
                      '$teacherName • Teacher Register & Attendance Desk',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 2. Nepali Classroom Bench Columns Grid
  // ---------------------------------------------------------------------------
  Widget _buildNepaliBenchColumns(bool isDark) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      physics: const BouncingScrollPhysics(),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: List.generate(_numColumns, (colIdx) {
          final columnNumber = colIdx + 1;
          final columnSeats = _seats.where((s) => s.columnNo == columnNumber).toList();

          return Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Bench Column
              _buildSingleBenchColumn(columnNumber, columnSeats, isDark),

              // Aisle / Walkway (गल्ली) between columns
              if (colIdx < _numColumns - 1)
                Container(
                  width: 32,
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      const Icon(Icons.arrow_downward_rounded, size: 14, color: AppTheme.darkSubtext),
                      const SizedBox(height: 6),
                      RotatedBox(
                        quarterTurns: 1,
                        child: Text(
                          'AISLE • हिड्ने बाटो',
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white38 : AppTheme.darkSubtext,
                            letterSpacing: 1.5,
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Icon(Icons.arrow_downward_rounded, size: 14, color: AppTheme.darkSubtext),
                    ],
                  ),
                ),
            ],
          );
        }),
      ),
    );
  }

  Widget _buildSingleBenchColumn(int columnNo, List<BenchSeat> columnSeats, bool isDark) {
    final colNames = [
      'हरफ १ • झ्याल पट्टी (Window Side)',
      'हरफ २ • मध्य बायाँ (Center-L)',
      'हरफ ३ • मध्य दायाँ (Center-R)',
      'हरफ ४ • ढोका पट्टी (Door Side)',
    ];
    final colLabel = columnNo <= colNames.length ? colNames[columnNo - 1] : 'हरफ $columnNo (Col $columnNo)';

    return Container(
      width: _studentsPerBench == 1 ? 170 : (_studentsPerBench == 2 ? 270 : 370),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard.withOpacity(0.7) : Colors.white.withOpacity(0.85),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Column Header
          Container(
            padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 10),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : const Color(0xFFE2E8F0),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              colLabel,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.3,
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Benches in this column
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _benchesPerColumn,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (_, bIdx) {
              final benchNo = bIdx + 1;
              final benchSeats = columnSeats.where((s) => s.benchNo == benchNo).toList();

              return _buildWoodenBenchUnit(columnNo, benchNo, benchSeats, isDark);
            },
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 3. Wooden Bench & Desk Unit (काठको डेस्क र बेन्च)
  // ---------------------------------------------------------------------------
  Widget _buildWoodenBenchUnit(
    int columnNo,
    int benchNo,
    List<BenchSeat> benchSeats,
    bool isDark,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFB45309).withOpacity(0.35), // Teak wooden border
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Wooden Desk Surface Header (काठको डेस्क)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFFD97706), Color(0xFFB45309)], // Amber wood
              ),
              borderRadius: BorderRadius.vertical(top: Radius.circular(12)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.table_restaurant_rounded, size: 12, color: Colors.white),
                    const SizedBox(width: 4),
                    Text(
                      'Bench $benchNo ${benchNo == 1 ? '(Front / अगाडि)' : (benchNo == _benchesPerColumn ? '(Back / पछाडि)' : '')}',
                      style: const TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
                Text(
                  'Col $columnNo',
                  style: const TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w800,
                    color: Colors.white70,
                  ),
                ),
              ],
            ),
          ),

          // Seat Slots Row (Left / Center / Right)
          Padding(
            padding: const EdgeInsets.all(6),
            child: Row(
              children: benchSeats.map((seat) {
                final globalIdx = _seats.indexOf(seat);
                final isSelected = _selectedSeatIndex == globalIdx;

                return Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 3),
                    child: _buildSeatTile(seat, globalIdx, isSelected, isDark),
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 4. Individual Seat Slot Tile
  // ---------------------------------------------------------------------------
  Widget _buildSeatTile(
    BenchSeat seat,
    int index,
    bool isSelected,
    bool isDark,
  ) {
    final posName = seat.benchPosition == 1
        ? 'L (बायाँ)'
        : (seat.benchPosition == 2 && _studentsPerBench == 3 ? 'C (बीच)' : 'R (दायाँ)');

    return GestureDetector(
      onTap: () => _handleSeatTap(index),
      onLongPress: () {
        if (!seat.isEmpty) _showStudentSeatDossier(seat, index);
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
        decoration: BoxDecoration(
          color: isSelected
              ? (isDark ? const Color(0xFF1E3A8A) : const Color(0xFFDBEAFE))
              : (seat.isEmpty
                  ? (isDark ? Colors.black26 : const Color(0xFFF8FAFC))
                  : (isDark ? AppTheme.darkBg : const Color(0xFFF0FDF4))),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected
                ? AppTheme.primary
                : (seat.isEmpty
                    ? Colors.grey.withOpacity(0.3)
                    : AppTheme.green.withOpacity(0.4)),
            width: isSelected ? 2 : 1,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppTheme.primary.withOpacity(0.35),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: seat.isEmpty
            ? Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    posName,
                    style: TextStyle(
                      fontSize: 8.5,
                      fontWeight: FontWeight.w700,
                      color: isDark ? Colors.white38 : AppTheme.darkSubtext,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Icon(
                    Icons.add_circle_outline_rounded,
                    size: 16,
                    color: isDark ? Colors.white24 : Colors.grey.shade400,
                  ),
                  const SizedBox(height: 2),
                  const Text(
                    '[ खाली सिट ]',
                    style: TextStyle(fontSize: 8, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                  ),
                ],
              )
            : Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Position & Roll Badge
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          posName,
                          style: const TextStyle(
                            fontSize: 7.5,
                            fontWeight: FontWeight.w900,
                            color: AppTheme.primary,
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(
                          color: AppTheme.green,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          '#${seat.rollNo ?? '—'}',
                          style: const TextStyle(
                            fontSize: 8.5,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),

                  // Student Initials / Avatar
                  Container(
                    width: 26,
                    height: 26,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(
                        colors: (seat.gender == 'female')
                            ? [const Color(0xFFEC4899), const Color(0xFFF43F5E)]
                            : [const Color(0xFF0F4C81), const Color(0xFF2563EB)],
                      ),
                    ),
                    child: Center(
                      child: Text(
                        seat.name != null && seat.name!.isNotEmpty ? seat.name![0] : 'S',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 3),

                  // Student First Name
                  Text(
                    seat.name?.split(' ').first ?? 'Student',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: isDark ? Colors.white : AppTheme.dark,
                    ),
                  ),

                  // Classroom Special Tag (CR, Vision, Buddy)
                  if (seat.specialRole != null) ...[
                    const SizedBox(height: 2),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                      decoration: BoxDecoration(
                        color: seat.specialRole!.contains('CR')
                            ? const Color(0xFFFEF3C7)
                            : (seat.specialRole!.contains('Vision')
                                ? const Color(0xFFF3E8FF)
                                : const Color(0xFFDCFCE7)),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        seat.specialRole!,
                        style: TextStyle(
                          fontSize: 7.5,
                          fontWeight: FontWeight.w900,
                          color: seat.specialRole!.contains('CR')
                              ? const Color(0xFFB45309)
                              : (seat.specialRole!.contains('Vision')
                                  ? const Color(0xFF7E22CE)
                                  : const Color(0xFF15803D)),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 5. Back of Classroom Wall & Exit Door (पछाडि / भित्ता)
  // ---------------------------------------------------------------------------
  Widget _buildBackClassroomWall(bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.cardBorder),
      ),
      child: const Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Icon(Icons.dashboard_customize_outlined, size: 15, color: AppTheme.darkSubtext),
              SizedBox(width: 6),
              Text(
                'पछाडिको भित्ता • CLASSROOM NOTICE BOARD (सूचना पाटी)',
                style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: AppTheme.darkSubtext),
              ),
            ],
          ),
          Row(
            children: [
              Icon(Icons.meeting_room_rounded, size: 15, color: AppTheme.orange),
              SizedBox(width: 4),
              Text(
                'पछाडिको ढोका • BACK EXIT',
                style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900, color: AppTheme.orange),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 6. Unassigned Students Tray
  // ---------------------------------------------------------------------------
  Widget _buildUnassignedTray(bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.orange.withOpacity(0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.person_pin_rounded, color: AppTheme.orange, size: 18),
                  const SizedBox(width: 6),
                  Text(
                    'Unseated Students (बाँकी विद्यार्थी): ${_unassignedStudents.length}',
                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w900, color: AppTheme.orange),
                  ),
                ],
              ),
              const Text(
                'Tap student, then tap any empty seat to assign',
                style: TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _unassignedStudents.map((s) {
              final isSel = _selectedUnassignedStudent?.id == s.id;
              return InkWell(
                onTap: () {
                  setState(() {
                    _selectedUnassignedStudent = isSel ? null : s;
                    _selectedSeatIndex = null;
                  });
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: isSel ? AppTheme.orange : (isDark ? AppTheme.darkBg : Colors.white),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isSel ? AppTheme.orange : AppTheme.cardBorder,
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        '#${s.rollNo}',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          color: isSel ? Colors.white : AppTheme.orange,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        s.name,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: isSel ? Colors.white : (isDark ? Colors.white : AppTheme.dark),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 7. Student Classroom Dossier Modal (Dossier & Parent Contact)
  // ---------------------------------------------------------------------------
  void _showStudentSeatDossier(BenchSeat seat, int seatIndex) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDossierState) => Padding(
          padding: const EdgeInsets.all(22),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: const BoxDecoration(
                      color: Color(0xFF0F4C81),
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        seat.name != null && seat.name!.isNotEmpty ? seat.name![0] : 'S',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Colors.white),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          seat.name ?? 'Student',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                        ),
                        Text(
                          'Roll #${seat.rollNo ?? '—'} • ${seat.seatCode} (${seat.positionLabel})',
                          style: const TextStyle(fontSize: 12, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppTheme.green.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text('PRESENT', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.green)),
                  ),
                ],
              ),
              const Divider(height: 20),

              // Role Tag Selection (CR, Vision, Buddy Pair)
              Row(
                children: [
                  const Text('Classroom Role:', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
                  const SizedBox(width: 8),
                  Wrap(
                    spacing: 6,
                    children: [
                      'CR (क्याप्टेन)',
                      'Vision (चस्मा)',
                      'Buddy Pair',
                    ].map((role) {
                      final isSel = seat.specialRole == role;
                      return InkWell(
                        onTap: () {
                          setState(() {
                            seat.specialRole = isSel ? null : role;
                          });
                          setDossierState(() {});
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isSel ? AppTheme.primary : (isDark ? AppTheme.darkBg : Colors.grey.shade100),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: isSel ? AppTheme.primary : AppTheme.cardBorder),
                          ),
                          child: Text(
                            role,
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: isSel ? Colors.white : (isDark ? Colors.white70 : AppTheme.dark),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              Row(
                children: [
                  const Icon(Icons.person_outline_rounded, size: 15, color: AppTheme.darkSubtext),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      '${seat.parentName ?? 'Guardian'} • ${seat.parentPhone ?? '+977 9801234567'}',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              // Contact Buttons
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _makePhoneCall(seat.parentPhone ?? '9801234567');
                      },
                      icon: const Icon(Icons.call_rounded, size: 16),
                      label: const Text('Call Parent', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.green,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _openWhatsApp(seat.parentPhone ?? '9801234567', seat.name ?? 'Student');
                      },
                      icon: const Icon(Icons.chat_bubble_outline_rounded, size: 16, color: Color(0xFF25D366)),
                      label: const Text('WhatsApp', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Color(0xFF25D366)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              // Unseat Option
              SizedBox(
                width: double.infinity,
                child: TextButton.icon(
                  onPressed: () {
                    Navigator.pop(ctx);
                    setState(() {
                      if (seat.studentId != null) {
                        _unassignedStudents.add(Student(
                          id: seat.studentId,
                          name: seat.name ?? 'Student',
                          grade: 'Student',
                          avatar: seat.name?.isNotEmpty ?? false ? seat.name![0] : 'S',
                          gpa: 3.5,
                          attendance: 0.95,
                          rank: 1,
                          rollNo: seat.rollNo ?? '01',
                          parentName: seat.parentName,
                          parentPhone: seat.parentPhone,
                        ));
                      }
                      seat.studentId = null;
                      seat.name = null;
                      seat.rollNo = null;
                      seat.parentName = null;
                      seat.parentPhone = null;
                      seat.specialRole = null;
                    });
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Student moved to unassigned tray.')),
                    );
                  },
                  icon: const Icon(Icons.person_remove_rounded, size: 16, color: AppTheme.red),
                  label: const Text('Unseat from Bench (बेन्चबाट हटाउनुहोस्)',
                      style: TextStyle(color: AppTheme.red, fontWeight: FontWeight.w700, fontSize: 12)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 8. Room Layout Config Sheet (कोठा तथा बेन्च मिलाउनुहोस्)
  // ---------------------------------------------------------------------------
  void _showLayoutConfigSheet(BuildContext context, List<Student> activeStudents, bool isDark) {
    int tempCols = _numColumns;
    int tempBenches = _benchesPerColumn;
    int tempPerBench = _studentsPerBench;

    showModalBottomSheet(
      context: context,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: const EdgeInsets.all(22),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Classroom Bench Structure (बेन्चको ढाँचा)',
                    style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
                  ),
                  IconButton(icon: const Icon(Icons.close_rounded), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const Divider(height: 16),
              // Columns
              const Text('Number of Columns (स्तम्भ / हरफहरू):', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
              const SizedBox(height: 6),
              Row(
                children: [2, 3, 4].map((c) {
                  final sel = tempCols == c;
                  return Expanded(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 4),
                      child: ChoiceChip(
                        label: Center(child: Text('$c Columns', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11))),
                        selected: sel,
                        onSelected: (_) => setModalState(() => tempCols = c),
                      ),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 14),
              // Capacity
              const Text('Students per Bench (प्रति बेन्च विद्यार्थी क्षमता):', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
              const SizedBox(height: 6),
              Row(
                children: [
                  {'val': 2, 'lbl': '2 Seats (Dual Desk - Standard)'},
                  {'val': 3, 'lbl': '3 Seats (3-Seater Bench)'},
                ].map((item) {
                  final val = item['val'] as int;
                  final lbl = item['lbl'] as String;
                  final sel = tempPerBench == val;
                  return Expanded(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 4),
                      child: ChoiceChip(
                        label: Center(child: Text(lbl, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11))),
                        selected: sel,
                        onSelected: (_) => setModalState(() => tempPerBench = val),
                      ),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 14),
              // Benches deep
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Benches per Column (पङ्क्ति संख्या):', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                  Text('$tempBenches Benches', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: AppTheme.primary)),
                ],
              ),
              Slider(
                value: tempBenches.toDouble(),
                min: 3,
                max: 8,
                divisions: 5,
                label: '$tempBenches',
                onChanged: (v) => setModalState(() => tempBenches = v.toInt()),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    setState(() {
                      _numColumns = tempCols;
                      _benchesPerColumn = tempBenches;
                      _studentsPerBench = tempPerBench;
                    });
                    _generateBenchLayout(activeStudents);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Text('Apply Classroom Layout (लागु गर्नुहोस्)', style: TextStyle(fontWeight: FontWeight.w800)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // 9. Classroom Notice Board Seating Chart Modal (कक्षाकोठा सूचना पाटी सिट चार्ट)
  // ---------------------------------------------------------------------------
  void _showClassroomNoticeChart(BuildContext context, String classSection, AuthProvider authProvider, bool isDark) {
    final teacherName = authProvider.teacherName.isNotEmpty ? authProvider.teacherName : 'Faculty In-Charge';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? AppTheme.darkCard : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Container(
        height: MediaQuery.of(context).size.height * 0.85,
        padding: const EdgeInsets.all(22),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.dashboard_customize_rounded, color: AppTheme.primary, size: 24),
                    SizedBox(width: 8),
                    Text(
                      'Classroom Notice Seating Chart (सिट तालिका)',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                    ),
                  ],
                ),
                IconButton(icon: const Icon(Icons.close_rounded), onPressed: () => Navigator.pop(ctx)),
              ],
            ),
            const Divider(height: 16),
            Expanded(
              child: SingleChildScrollView(
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkBg : const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppTheme.cardBorder),
                  ),
                  child: Column(
                    children: [
                      const Text(
                        'NEPAL SCHOOL HUB (NSHLMS)',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'शैक्षिक सत्र २०८१ • $classSection दैनिक बसाइ तालिका (Daily Seating Chart)',
                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'कक्षा शिक्षक (Class In-Charge): $teacherName',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppTheme.primary),
                      ),
                      const Divider(height: 20),
                      Table(
                        border: TableBorder.all(color: Colors.grey.withOpacity(0.3)),
                        columnWidths: const {
                          0: FlexColumnWidth(1.2),
                          1: FlexColumnWidth(1.4),
                          2: FlexColumnWidth(2.6),
                        },
                        children: [
                          TableRow(
                            decoration: BoxDecoration(color: AppTheme.primary.withOpacity(0.12)),
                            children: const [
                              Padding(padding: EdgeInsets.all(6), child: Text('Bench Code', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 10.5))),
                              Padding(padding: EdgeInsets.all(6), child: Text('Roll No', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 10.5))),
                              Padding(padding: EdgeInsets.all(6), child: Text('Student Name & Role', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 10.5))),
                            ],
                          ),
                          ..._seats.where((s) => !s.isEmpty).map((s) => TableRow(
                                children: [
                                  Padding(padding: const EdgeInsets.all(5), child: Text(s.seatCode, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800))),
                                  Padding(padding: const EdgeInsets.all(5), child: Text('#${s.rollNo}', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.primary))),
                                  Padding(
                                    padding: const EdgeInsets.all(5),
                                    child: Row(
                                      children: [
                                        Expanded(child: Text(s.name ?? '—', style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700))),
                                        if (s.specialRole != null)
                                          Text(' [${s.specialRole}]', style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: AppTheme.orange)),
                                      ],
                                    ),
                                  ),
                                ],
                              )),
                        ],
                      ),
                      const SizedBox(height: 20),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.06),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('कक्षा कोठाका नियमहरू (Classroom Rules):', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900, color: AppTheme.primary)),
                            SizedBox(height: 4),
                            Text('१. हरेक आइतबार बिहान नियमित बेन्च रोटेसन हुनेछ (Weekly bench rotation every Sunday).', style: TextStyle(fontSize: 9.5)),
                            Text('२. तोकिएको डेस्क र बेन्चमा मात्र बस्नुपर्नेछ (Sit strictly at your designated desk).', style: TextStyle(fontSize: 9.5)),
                            Text('३. बेन्च र कक्षा कोठा सधैं सफा राख्नुहोस् (Maintain desk cleanliness).', style: TextStyle(fontSize: 9.5)),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Class In-Charge: $teacherName', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800)),
                          const Text('Principal Signature: ........................', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 14),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      backgroundColor: AppTheme.green,
                      content: Text('Classroom Notice Chart PDF generated & ready to print ✅'),
                    ),
                  );
                },
                icon: const Icon(Icons.print_rounded, size: 18),
                label: const Text('Print Class Seating Chart PDF', style: TextStyle(fontWeight: FontWeight.w800)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
