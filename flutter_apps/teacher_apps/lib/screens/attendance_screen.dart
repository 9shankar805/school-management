import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../providers/teacher_provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../services/api_service.dart';

class AttendanceScreen extends StatefulWidget {
  final bool startInSpeedCall;
  const AttendanceScreen({super.key, this.startInSpeedCall = false});
  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  final ApiService _apiService = ApiService();
  int _selectedClass = 0;
  // 0=Present, 1=Absent, 2=Late, 3=Leave
  List<int> _status = [];
  List<String?> _remarks = [];
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();
  bool _isGridView = false;
  String _currentNepaliDate = '१० असोज २०८१ • बिहीबार (Today)';
  String? _lastLoadedClassKey;
  bool _loadingServerData = false;
  bool _speedCallAutoTriggered = false;

  String _toNepaliNumber(String input) {
    const nepaliDigits = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
    return input.replaceAllMapped(RegExp(r'\d'), (m) => nepaliDigits[int.parse(m[0]!)]);
  }

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

  Future<void> _makePhoneCall(String phoneNumber) async {
    final clean = phoneNumber.replaceAll(RegExp(r'[^\d+]'), '');
    final Uri launchUri = Uri(scheme: 'tel', path: clean);
    try {
      if (await canLaunchUrl(launchUri)) {
        await launchUrl(launchUri);
      } else {
        await launchUrl(launchUri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Calling $phoneNumber (Dial: $clean)'),
            backgroundColor: AppTheme.primary,
          ),
        );
      }
    }
  }

  Future<void> _openWhatsApp(String phoneNumber, String studentName) async {
    final clean = phoneNumber.replaceAll(RegExp(r'[^\d]'), '');
    final message = Uri.encodeComponent(
      'Namaste! This is from the school regarding $studentName\'s attendance today ($_currentNepaliDate). Please let us know the reason for absence.',
    );
    final Uri waUri = Uri.parse('https://wa.me/$clean?text=$message');
    try {
      await launchUrl(waUri, mode: LaunchMode.externalApplication);
    } catch (_) {
      final smsUri = Uri(scheme: 'sms', path: clean, queryParameters: {'body': 'Namaste, regarding $studentName attendance today.'});
      await launchUrl(smsUri);
    }
  }

  Future<void> _loadServerAttendance(String classId, List<Student> activeStudents) async {
    setState(() => _loadingServerData = true);
    try {
      final todayStr = DateTime.now().toIso8601String().substring(0, 10);
      final res = await _apiService.getAttendanceByDate(classId, todayStr);
      if (res.success && res.data != null && mounted) {
        final records = res.data!['attendance'] as List? ?? [];
        if (records.isNotEmpty) {
          final statusMap = <int, int>{};
          final remarkMap = <int, String>{};
          for (var r in records) {
            if (r is Map && r['student_id'] != null) {
              final sId = r['student_id'] is int ? r['student_id'] as int : int.tryParse(r['student_id'].toString()) ?? 0;
              final stStr = (r['status'] ?? '').toString();
              int code = 0;
              if (stStr == 'absent' || stStr == 'off') code = 1;
              else if (stStr == 'late') code = 2;
              else if (stStr == 'excused' || stStr == 'leave') code = 3;
              statusMap[sId] = code;
              if (r['remarks'] != null && r['remarks'].toString().isNotEmpty) {
                remarkMap[sId] = r['remarks'].toString();
              }
            }
          }
          setState(() {
            for (int i = 0; i < activeStudents.length; i++) {
              final sId = activeStudents[i].id;
              if (sId != null && statusMap.containsKey(sId)) {
                _status[i] = statusMap[sId]!;
                if (remarkMap.containsKey(sId)) {
                  _remarks[i] = remarkMap[sId];
                }
              }
            }
          });
        }
      }
    } catch (_) {}
    if (mounted) {
      setState(() => _loadingServerData = false);
    }
  }

  void _openSpeedRollCall(List<Student> activeStudents, {int initialIndex = 0}) {
    if (activeStudents.isEmpty) return;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final cls = classesList.isNotEmpty && _selectedClass < classesList.length
        ? classesList[_selectedClass]
        : AppData.classes.first;

    int currentIndex = initialIndex.clamp(0, activeStudents.length - 1);
    bool autoAdvance = true;
    bool soundEnabled = true;
    bool isCompleted = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      enableDrag: false,
      builder: (sheetCtx) => StatefulBuilder(
        builder: (context, setModalState) {
          final total = activeStudents.length;
          final s = activeStudents[currentIndex];
          final currentStatus = currentIndex < _status.length ? _status[currentIndex] : 0;
          final currentRemark = currentIndex < _remarks.length ? _remarks[currentIndex] : null;

          void applyStatus(int newStatus) {
            if (soundEnabled) {
              HapticFeedback.mediumImpact();
              SystemSound.play(SystemSoundType.click);
            }
            _setStatus(currentIndex, newStatus);
            setModalState(() {});

            if (autoAdvance) {
              Future.delayed(const Duration(milliseconds: 220), () {
                if (!mounted) return;
                if (currentIndex < total - 1) {
                  setModalState(() {
                    currentIndex++;
                  });
                } else {
                  setModalState(() {
                    isCompleted = true;
                  });
                }
              });
            } else {
              if (currentIndex >= total - 1) {
                setModalState(() {
                  isCompleted = true;
                });
              }
            }
          }

          void showRollJumpDialog() {
            showModalBottomSheet(
              context: context,
              backgroundColor: Colors.transparent,
              builder: (pickerCtx) => Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkCard : Colors.white,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.pin_rounded, color: AppTheme.primary, size: 20),
                        const SizedBox(width: 8),
                        const Text(
                          'Jump to Roll Number (रोल नं छान्नुहोस्)',
                          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
                        ),
                        const Spacer(),
                        IconButton(
                          icon: const Icon(Icons.close_rounded, size: 20),
                          onPressed: () => Navigator.pop(pickerCtx),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: List.generate(total, (i) {
                        final st = activeStudents[i];
                        final code = i < _status.length ? _status[i] : 0;
                        Color bg = AppTheme.green;
                        if (code == 1) bg = AppTheme.red;
                        else if (code == 2) bg = AppTheme.orange;
                        else if (code == 3) bg = AppTheme.purple;
                        final isCur = i == currentIndex;

                        return InkWell(
                          onTap: () {
                            Navigator.pop(pickerCtx);
                            setModalState(() {
                              currentIndex = i;
                              isCompleted = false;
                            });
                          },
                          borderRadius: BorderRadius.circular(10),
                          child: Container(
                            width: 44,
                            height: 44,
                            decoration: BoxDecoration(
                              color: bg.withOpacity(isCur ? 1.0 : 0.15),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: isCur ? Colors.white : bg,
                                width: isCur ? 2.5 : 1,
                              ),
                            ),
                            child: Center(
                              child: Text(
                                st.rollNo,
                                style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 13,
                                  color: isCur ? Colors.white : (isDark ? Colors.white : bg),
                                ),
                              ),
                            ),
                          ),
                        );
                      }),
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            );
          }

          if (isCompleted) {
            return Container(
              height: MediaQuery.of(context).size.height * 0.88,
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
              ),
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
              child: Column(
                children: [
                  Container(
                    width: 44,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.withOpacity(0.3),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: AppTheme.green.withOpacity(0.12),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.check_circle_rounded, color: AppTheme.green, size: 54),
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Speed Roll Call Completed! 🎉',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'All $total students in ${cls.section} have been marked for today.',
                    style: const TextStyle(fontSize: 12.5, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      _buildSpecCard('Present (उपस्थित)', '$_presentCount', AppTheme.green, isDark),
                      const SizedBox(width: 8),
                      _buildSpecCard('Absent (अनुपस्थित)', '$_absentCount', AppTheme.red, isDark),
                      const SizedBox(width: 8),
                      _buildSpecCard('Late (ढिलो)', '$_lateCount', AppTheme.orange, isDark),
                      const SizedBox(width: 8),
                      _buildSpecCard('Leave (बिदा)', '$_leaveCount', AppTheme.purple, isDark),
                    ],
                  ),
                  const SizedBox(height: 18),
                  if (_absentCount > 0) ...[
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.red.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.red.withOpacity(0.3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.bolt_rounded, color: Colors.red, size: 24),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '$_absentCount Student${_absentCount > 1 ? 's' : ''} are Absent!',
                                  style: const TextStyle(fontWeight: FontWeight.w900, color: Colors.red, fontSize: 13),
                                ),
                                const Text(
                                  'Speed Call parents immediately to record reasons or trigger SMS',
                                  style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                                ),
                              ],
                            ),
                          ),
                          ElevatedButton.icon(
                            onPressed: () {
                              Navigator.pop(sheetCtx);
                              _openSpeedCallSheet(activeStudents);
                            },
                            icon: const Icon(Icons.phone_in_talk_rounded, size: 14),
                            label: const Text('Speed Call', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                  const Spacer(),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () {
                            setModalState(() {
                              currentIndex = 0;
                              isCompleted = false;
                            });
                          },
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          child: const Text('Review Again', style: TextStyle(fontWeight: FontWeight.w800)),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton.icon(
                          onPressed: () {
                            Navigator.pop(sheetCtx);
                            _showSaveDialog();
                          },
                          icon: const Icon(Icons.cloud_upload_outlined, size: 18),
                          label: const Text('Submit Haziri to Server', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.primary,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            );
          }

          return Container(
            height: MediaQuery.of(context).size.height * 0.94,
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.25),
                  blurRadius: 24,
                  offset: const Offset(0, -6),
                ),
              ],
            ),
            child: Column(
              children: [
                Container(
                  width: 44,
                  height: 4,
                  margin: const EdgeInsets.only(top: 10, bottom: 6),
                  decoration: BoxDecoration(
                    color: Colors.grey.withOpacity(0.3),
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),

                // Top Header Row
                Padding(
                  padding: const EdgeInsets.fromLTRB(18, 4, 18, 10),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(7),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF2563EB), Color(0xFF4F46E5)],
                          ),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.bolt_rounded, color: Colors.amber, size: 20),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Text(
                                  'Speed Call (द्रुत हाजिरी)',
                                  style: TextStyle(
                                    fontSize: 15.5,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: AppTheme.primary.withOpacity(0.12),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    cls.section,
                                    style: const TextStyle(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w800,
                                      color: AppTheme.primary,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            Text(
                              'Roll Call Student ${currentIndex + 1} of $total',
                              style: const TextStyle(
                                fontSize: 11.5,
                                color: AppTheme.darkSubtext,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),

                      InkWell(
                        onTap: () => setModalState(() => autoAdvance = !autoAdvance),
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                          decoration: BoxDecoration(
                            color: autoAdvance ? AppTheme.primary.withOpacity(0.12) : Colors.grey.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: autoAdvance ? AppTheme.primary : Colors.grey.withOpacity(0.3),
                              width: 1,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                autoAdvance ? Icons.flash_on_rounded : Icons.flash_off_rounded,
                                size: 13,
                                color: autoAdvance ? AppTheme.primary : AppTheme.darkSubtext,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                autoAdvance ? 'Auto' : 'Manual',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: autoAdvance ? AppTheme.primary : AppTheme.darkSubtext,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),

                      IconButton(
                        icon: Icon(
                          soundEnabled ? Icons.volume_up_rounded : Icons.volume_off_rounded,
                          size: 20,
                          color: soundEnabled ? AppTheme.primary : AppTheme.darkSubtext,
                        ),
                        onPressed: () => setModalState(() => soundEnabled = !soundEnabled),
                        tooltip: soundEnabled ? 'Sound ON' : 'Sound OFF',
                      ),

                      IconButton(
                        icon: const Icon(Icons.close_rounded, size: 22),
                        onPressed: () => Navigator.pop(sheetCtx),
                      ),
                    ],
                  ),
                ),

                // Animated Progress Bar
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: LinearProgressIndicator(
                      value: (currentIndex + 1) / total,
                      minHeight: 5,
                      backgroundColor: isDark ? AppTheme.darkBg : const Color(0xFFE2E8F0),
                      valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primary),
                    ),
                  ),
                ),
                const SizedBox(height: 8),

                // Live Count Pills
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _buildMiniBadge('P: $_presentCount', AppTheme.green),
                      _buildMiniBadge('A: $_absentCount', AppTheme.red),
                      _buildMiniBadge('L: $_lateCount', AppTheme.orange),
                      _buildMiniBadge('Lv: $_leaveCount', AppTheme.purple),
                      InkWell(
                        onTap: showRollJumpDialog,
                        child: Row(
                          children: [
                            const Icon(Icons.grid_4x4_rounded, size: 14, color: AppTheme.primary),
                            const SizedBox(width: 3),
                            Text(
                              'All Rolls ($total)',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),

                // Swipeable Student Card
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 18),
                    child: GestureDetector(
                      onHorizontalDragEnd: (details) {
                        if (details.primaryVelocity != null) {
                          if (details.primaryVelocity! > 200) {
                            applyStatus(0); // Swipe Right -> Present
                          } else if (details.primaryVelocity! < -200) {
                            applyStatus(1); // Swipe Left -> Absent
                          }
                        }
                      },
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 250),
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkBg : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(
                            color: currentStatus == 0
                                ? AppTheme.green.withOpacity(0.5)
                                : (currentStatus == 1
                                    ? AppTheme.red.withOpacity(0.5)
                                    : (currentStatus == 2
                                        ? AppTheme.orange.withOpacity(0.5)
                                        : (currentStatus == 3
                                            ? AppTheme.purple.withOpacity(0.5)
                                            : AppTheme.cardBorder))),
                            width: 2,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 12,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            // 1. Top Roll & Status Header
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                                  decoration: BoxDecoration(
                                    color: AppTheme.primary,
                                    borderRadius: BorderRadius.circular(12),
                                    boxShadow: [
                                      BoxShadow(
                                        color: AppTheme.primary.withOpacity(0.3),
                                        blurRadius: 6,
                                        offset: const Offset(0, 2),
                                      ),
                                    ],
                                  ),
                                  child: Text(
                                    'ROLL #${s.rollNo} • ${_toNepaliNumber(s.rollNo)}',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w900,
                                      fontSize: 12.5,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ),
                                _buildCurrentStatusChip(currentStatus),
                              ],
                            ),

                            // 2. Center Content (Avatar, Name, Details - Scrollable if compact, centered if large)
                            Expanded(
                              child: Center(
                                child: SingleChildScrollView(
                                  physics: const BouncingScrollPhysics(),
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(vertical: 4),
                                    child: Column(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        // Student Avatar
                                        Container(
                                          width: 70,
                                          height: 70,
                                          decoration: BoxDecoration(
                                            shape: BoxShape.circle,
                                            gradient: LinearGradient(
                                              colors: currentStatus == 0
                                                  ? [AppTheme.green, const Color(0xFF059669)]
                                                  : (currentStatus == 1
                                                      ? [AppTheme.red, const Color(0xFFDC2626)]
                                                      : (currentStatus == 2
                                                          ? [AppTheme.orange, const Color(0xFFD97706)]
                                                          : [const Color(0xFF0F4C81), const Color(0xFF2563EB)])),
                                            ),
                                            boxShadow: [
                                              BoxShadow(
                                                color: (currentStatus == 0 ? AppTheme.green : AppTheme.primary).withOpacity(0.25),
                                                blurRadius: 10,
                                                offset: const Offset(0, 3),
                                              ),
                                            ],
                                          ),
                                          child: Center(
                                            child: Text(
                                              s.name.isNotEmpty
                                                  ? s.name.split(' ').map((e) => e.isNotEmpty ? e[0] : '').take(2).join()
                                                  : 'ST',
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontWeight: FontWeight.w900,
                                                fontSize: 20,
                                                letterSpacing: 1,
                                              ),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 8),

                                        // Student Name
                                        Text(
                                          s.name,
                                          textAlign: TextAlign.center,
                                          style: TextStyle(
                                            fontSize: 17,
                                            fontWeight: FontWeight.w900,
                                            color: isDark ? Colors.white : AppTheme.dark,
                                            letterSpacing: -0.3,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 3),

                                        // Class & Attendance
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              'Class ${cls.section} • ID: ${s.id ?? (currentIndex + 1)}',
                                              style: const TextStyle(
                                                fontSize: 11.5,
                                                fontWeight: FontWeight.w600,
                                                color: AppTheme.darkSubtext,
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: (s.attendance >= 0.85 ? AppTheme.green : AppTheme.orange).withOpacity(0.12),
                                                borderRadius: BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                '${(s.attendance * 100).toInt()}% Attendance',
                                                style: TextStyle(
                                                  fontSize: 10,
                                                  fontWeight: FontWeight.w800,
                                                  color: s.attendance >= 0.85 ? AppTheme.green : AppTheme.orange,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),

                                        // Parent contact
                                        if (s.parentPhone.isNotEmpty && s.parentPhone != '-') ...[
                                          const SizedBox(height: 6),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                            decoration: BoxDecoration(
                                              color: isDark ? AppTheme.darkCard : Colors.white,
                                              borderRadius: BorderRadius.circular(10),
                                              border: Border.all(
                                                color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                                                width: 0.8,
                                              ),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                const Icon(Icons.person_pin_circle_rounded, size: 13, color: AppTheme.primary),
                                                const SizedBox(width: 5),
                                                Flexible(
                                                  child: Text(
                                                    '${s.parentName.isNotEmpty ? s.parentName : "Parent"} (${s.parentPhone})',
                                                    style: const TextStyle(
                                                      fontSize: 11,
                                                      fontWeight: FontWeight.w600,
                                                      color: AppTheme.darkSubtext,
                                                    ),
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                                const SizedBox(width: 6),
                                                InkWell(
                                                  onTap: () => _makePhoneCall(s.parentPhone),
                                                  child: Container(
                                                    padding: const EdgeInsets.all(4),
                                                    decoration: BoxDecoration(
                                                      color: AppTheme.green.withOpacity(0.12),
                                                      shape: BoxShape.circle,
                                                    ),
                                                    child: const Icon(Icons.phone_rounded, size: 11, color: AppTheme.green),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],

                                        if (currentRemark != null && currentRemark.isNotEmpty) ...[
                                          const SizedBox(height: 5),
                                          Text(
                                            'Note: $currentRemark',
                                            style: const TextStyle(
                                              fontSize: 10.5,
                                              color: AppTheme.orange,
                                              fontStyle: FontStyle.italic,
                                              fontWeight: FontWeight.w700,
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ),

                            // 3. Swipe hints
                            const Padding(
                              padding: EdgeInsets.only(top: 4),
                              child: Text(
                                '👉 Swipe Right for Present • 👈 Swipe Left for Absent',
                                style: TextStyle(
                                  fontSize: 10,
                                  color: AppTheme.darkSubtext,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),

                // Speed Action Pad (Big Thumb Buttons)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 4,
                        child: _buildBigSpeedButton(
                          title: 'PRESENT',
                          nepali: 'उपस्थित (P)',
                          icon: Icons.check_circle_rounded,
                          color: AppTheme.green,
                          isSelected: currentStatus == 0,
                          onTap: () => applyStatus(0),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        flex: 4,
                        child: _buildBigSpeedButton(
                          title: 'ABSENT',
                          nepali: 'अनुपस्थित (A)',
                          icon: Icons.cancel_rounded,
                          color: AppTheme.red,
                          isSelected: currentStatus == 1,
                          onTap: () => applyStatus(1),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        flex: 3,
                        child: _buildBigSpeedButton(
                          title: 'LATE',
                          nepali: 'ढिलो (L)',
                          icon: Icons.access_time_filled_rounded,
                          color: AppTheme.orange,
                          isSelected: currentStatus == 2,
                          onTap: () => applyStatus(2),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        flex: 3,
                        child: _buildBigSpeedButton(
                          title: 'LEAVE',
                          nepali: 'बिदा (Lv)',
                          icon: Icons.event_busy_rounded,
                          color: AppTheme.purple,
                          isSelected: currentStatus == 3,
                          onTap: () => applyStatus(3),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 10),

                // Bottom Navigation Bar
                Padding(
                  padding: const EdgeInsets.fromLTRB(18, 0, 18, 14),
                  child: Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: currentIndex > 0
                              ? () => setModalState(() => currentIndex--)
                              : null,
                          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 14),
                          label: const Text('Prev', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        onPressed: () {
                          _showRemarkDialog(currentIndex);
                        },
                        icon: const Icon(Icons.edit_note_rounded, color: AppTheme.primary),
                        tooltip: 'Add Remark',
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () {
                            if (currentIndex < total - 1) {
                              setModalState(() => currentIndex++);
                            } else {
                              setModalState(() => isCompleted = true);
                            }
                          },
                          icon: Icon(currentIndex < total - 1 ? Icons.arrow_forward_ios_rounded : Icons.done_all_rounded, size: 14),
                          label: Text(
                            currentIndex < total - 1 ? 'Skip / Next' : 'Finish ✔',
                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: currentIndex < total - 1 ? AppTheme.primary : AppTheme.green,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSpecCard(String title, String value, Color color, bool isDark) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
        decoration: BoxDecoration(
          color: color.withOpacity(isDark ? 0.2 : 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
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

  Widget _buildMiniBadge(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withOpacity(0.25), width: 0.8),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w800,
          color: color,
        ),
      ),
    );
  }

  Widget _buildCurrentStatusChip(int status) {
    Color color;
    String label;
    if (status == 0) {
      color = AppTheme.green;
      label = 'PRESENT ✅';
    } else if (status == 1) {
      color = AppTheme.red;
      label = 'ABSENT ❌';
    } else if (status == 2) {
      color = AppTheme.orange;
      label = 'LATE ⏱️';
    } else {
      color = AppTheme.purple;
      label = 'LEAVE 📝';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color, width: 1.2),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontWeight: FontWeight.w900,
          fontSize: 11,
          letterSpacing: 0.4,
        ),
      ),
    );
  }

  Widget _buildBigSpeedButton({
    required String title,
    required String nepali,
    required IconData icon,
    required Color color,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 4),
          decoration: BoxDecoration(
            gradient: isSelected
                ? LinearGradient(
                    colors: [color, color.withOpacity(0.85)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  )
                : null,
            color: isSelected ? null : color.withOpacity(0.08),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isSelected ? color : color.withOpacity(0.35),
              width: isSelected ? 2.5 : 1.2,
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: color.withOpacity(0.35),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ]
                : null,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                color: isSelected ? Colors.white : color,
                size: 22,
              ),
              const SizedBox(height: 4),
              Text(
                title,
                style: TextStyle(
                  color: isSelected ? Colors.white : color,
                  fontWeight: FontWeight.w900,
                  fontSize: 11,
                  letterSpacing: 0.3,
                ),
              ),
              Text(
                nepali,
                style: TextStyle(
                  color: isSelected ? Colors.white70 : color.withOpacity(0.75),
                  fontWeight: FontWeight.w700,
                  fontSize: 8.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openSpeedCallSheet(List<Student> activeStudents) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final absentEntries = activeStudents
              .asMap()
              .entries
              .where((e) => e.key < _status.length && _status[e.key] == 1)
              .toList();

          return Container(
            height: MediaQuery.of(context).size.height * 0.78,
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.2),
                  blurRadius: 20,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: Column(
              children: [
                Container(
                  width: 44,
                  height: 4,
                  margin: const EdgeInsets.only(top: 12, bottom: 8),
                  decoration: BoxDecoration(
                    color: Colors.grey.withOpacity(0.3),
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 14),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.red.withOpacity(0.12),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.bolt_rounded, color: Colors.red, size: 24),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Speed Call Absentees (द्रुत कल)',
                              style: TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                            Text(
                              '${absentEntries.length} student(s) absent today • Direct Parent Dialing',
                              style: const TextStyle(
                                fontSize: 12,
                                color: AppTheme.darkSubtext,
                                fontWeight: FontWeight.w600,
                              ),
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
                ),
                const Divider(height: 1),
                Expanded(
                  child: absentEntries.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.check_circle_outline_rounded, size: 54, color: AppTheme.green),
                              const SizedBox(height: 12),
                              const Text(
                                'Great News! No Absent Students',
                                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'All students in this class are marked present.',
                                style: TextStyle(fontSize: 12, color: AppTheme.darkSubtext),
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.fromLTRB(18, 14, 18, 24),
                          itemCount: absentEntries.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (_, i) {
                            final entry = absentEntries[i];
                            final origIndex = entry.key;
                            final s = entry.value;
                            final currentRemark = origIndex < _remarks.length ? _remarks[origIndex] : null;

                            return Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: isDark ? AppTheme.darkBg : const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(
                                  color: Colors.red.withOpacity(0.2),
                                  width: 1,
                                ),
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
                                          color: Colors.red.withOpacity(0.12),
                                          shape: BoxShape.circle,
                                        ),
                                        child: Center(
                                          child: Text(
                                            s.rollNo,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w900,
                                              color: Colors.red,
                                              fontSize: 14,
                                            ),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              s.name,
                                              style: const TextStyle(
                                                fontSize: 14.5,
                                                fontWeight: FontWeight.w900,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Row(
                                              children: [
                                                const Icon(Icons.person_outline_rounded, size: 13, color: AppTheme.darkSubtext),
                                                const SizedBox(width: 4),
                                                Expanded(
                                                  child: Text(
                                                    '${s.parentName} • ${s.parentPhone}',
                                                    style: const TextStyle(
                                                      fontSize: 11.5,
                                                      fontWeight: FontWeight.w600,
                                                      color: AppTheme.darkSubtext,
                                                    ),
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: ElevatedButton.icon(
                                          onPressed: () => _makePhoneCall(s.parentPhone),
                                          icon: const Icon(Icons.call_rounded, size: 16),
                                          label: const Text('Call Parent', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: AppTheme.green,
                                            foregroundColor: Colors.white,
                                            padding: const EdgeInsets.symmetric(vertical: 10),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: OutlinedButton.icon(
                                          onPressed: () => _openWhatsApp(s.parentPhone, s.name),
                                          icon: const Icon(Icons.chat_bubble_outline_rounded, size: 16, color: Color(0xFF25D366)),
                                          label: const Text('WhatsApp / SMS', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                                          style: OutlinedButton.styleFrom(
                                            foregroundColor: isDark ? Colors.white : AppTheme.dark,
                                            side: const BorderSide(color: Color(0xFF25D366), width: 1.2),
                                            padding: const EdgeInsets.symmetric(vertical: 10),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),
                                  Wrap(
                                    spacing: 6,
                                    runSpacing: 6,
                                    children: [
                                      'Sick / बिरामी',
                                      'Family Event',
                                      'Out of Town',
                                      'No Answer / अनुत्तरित',
                                      'Will Come Tomorrow',
                                    ].map((reason) {
                                      final isSelected = currentRemark == reason;
                                      return InkWell(
                                        onTap: () {
                                          setState(() => _remarks[origIndex] = isSelected ? null : reason);
                                          setModalState(() {});
                                        },
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: isSelected ? AppTheme.primary : (isDark ? AppTheme.darkCard : Colors.white),
                                            borderRadius: BorderRadius.circular(8),
                                            border: Border.all(
                                              color: isSelected ? AppTheme.primary : AppTheme.cardBorder,
                                              width: 0.8,
                                            ),
                                          ),
                                          child: Text(
                                            reason,
                                            style: TextStyle(
                                              fontSize: 10.5,
                                              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                                              color: isSelected ? Colors.white : (isDark ? Colors.white70 : AppTheme.dark),
                                            ),
                                          ),
                                        ),
                                      );
                                    }).toList(),
                                  ),
                                  const SizedBox(height: 10),
                                  // Quick Reclassify status options
                                  Row(
                                    children: [
                                      const Text(
                                        'Reclassify:',
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w700,
                                          color: AppTheme.darkSubtext,
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      InkWell(
                                        onTap: () {
                                          _setStatus(origIndex, 3); // Leave
                                          setModalState(() {});
                                        },
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                                          decoration: BoxDecoration(
                                            color: AppTheme.purple.withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(6),
                                            border: Border.all(color: AppTheme.purple.withOpacity(0.35)),
                                          ),
                                          child: const Text('Mark Leave', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.purple)),
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      InkWell(
                                        onTap: () {
                                          _setStatus(origIndex, 2); // Late
                                          setModalState(() {});
                                        },
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                                          decoration: BoxDecoration(
                                            color: AppTheme.orange.withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(6),
                                            border: Border.all(color: AppTheme.orange.withOpacity(0.35)),
                                          ),
                                          child: const Text('Mark Late', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.orange)),
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      InkWell(
                                        onTap: () {
                                          _setStatus(origIndex, 0); // Present
                                          setModalState(() {});
                                        },
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                                          decoration: BoxDecoration(
                                            color: AppTheme.green.withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(6),
                                            border: Border.all(color: AppTheme.green.withOpacity(0.35)),
                                          ),
                                          child: const Text('Mark Present', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.green)),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

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
      final cId = '${cls.classId ?? cls.id ?? 1}';
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _loadServerAttendance(cId, activeStudents);
      });
    } else if (_status.length != activeStudents.length) {
      _syncStatusLength(activeStudents.length);
    }

    if (widget.startInSpeedCall && !_speedCallAutoTriggered && activeStudents.isNotEmpty) {
      _speedCallAutoTriggered = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _openSpeedRollCall(activeStudents);
      });
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
          // Speed Roll Call Interactive Mode Button
          Container(
            margin: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
            child: InkWell(
              onTap: () => _openSpeedRollCall(activeStudents),
              borderRadius: BorderRadius.circular(10),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
                  ),
                  borderRadius: BorderRadius.circular(10),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF2563EB).withOpacity(0.35),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.bolt_rounded, size: 16, color: Colors.amber),
                    SizedBox(width: 4),
                    Text(
                      'Speed Call',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          // Speed Call Absentees Quick Launcher (if absentees > 0)
          if (_absentCount > 0)
            IconButton(
              icon: Badge(
                label: Text('$_absentCount',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 10)),
                backgroundColor: Colors.red,
                child: const Icon(Icons.phone_in_talk_rounded, size: 20, color: Colors.red),
              ),
              tooltip: 'Speed Call Absentees ($_absentCount)',
              onPressed: () => _openSpeedCallSheet(activeStudents),
            ),
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
          if (_loadingServerData)
            const LinearProgressIndicator(
              minHeight: 2.5,
              backgroundColor: Colors.transparent,
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primary),
            ),
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
                      Flexible(
                        child: GestureDetector(
                          onTap: () => _showDatePicker(context, isDark),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.calendar_month_rounded,
                                  size: 15, color: AppTheme.primary),
                              const SizedBox(width: 6),
                              Flexible(
                                child: Text(
                                  _currentNepaliDate,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w800,
                                    color: AppTheme.primary,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 4),
                              const Icon(Icons.arrow_drop_down,
                                  size: 16, color: AppTheme.primary),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
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

                  // Fast Actions (Speed Call, Mark All Present & Reset)
                  Row(
                    children: [
                      Expanded(
                        flex: 6,
                        child: ElevatedButton.icon(
                          onPressed: () => _openSpeedRollCall(activeStudents),
                          icon: const Icon(Icons.bolt_rounded, size: 17, color: Colors.amber),
                          label: const Text(
                            '⚡ Speed Call (द्रुत हाजिरी)',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: 0.2,
                            ),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF2563EB),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 9.5),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 3,
                            shadowColor: const Color(0xFF2563EB).withOpacity(0.4),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        flex: 5,
                        child: OutlinedButton.icon(
                          onPressed: () => _markAll(0),
                          icon: const Icon(Icons.done_all_rounded,
                              size: 15, color: AppTheme.green),
                          label: const Text(
                            'All Present',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                              color: AppTheme.green,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            side: BorderSide(
                                color: AppTheme.green.withOpacity(0.4)),
                            padding: const EdgeInsets.symmetric(vertical: 9.5),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),
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

          // Speed Call Quick Dial Alert Banner
          if (_absentCount > 0)
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 0, 18, 10),
              child: InkWell(
                onTap: () => _openSpeedCallSheet(activeStudents),
                borderRadius: BorderRadius.circular(14),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: isDark
                          ? [const Color(0xFF450A0A), const Color(0xFF7F1D1D)]
                          : [const Color(0xFFFEF2F2), const Color(0xFFFEE2E2)],
                    ),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: Colors.red.withOpacity(0.35),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(7),
                        decoration: const BoxDecoration(
                          color: Colors.red,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.bolt_rounded, color: Colors.white, size: 18),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Speed Call: $_absentCount Student${_absentCount > 1 ? 's' : ''} Absent Today',
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w900,
                                color: isDark ? Colors.red.shade200 : const Color(0xFF991B1B),
                              ),
                            ),
                            Text(
                              'Tap here to quickly call parents or log absence remarks',
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.red.shade300 : const Color(0xFFB91C1C),
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.phone_in_talk_rounded, size: 12, color: Colors.white),
                            SizedBox(width: 4),
                            Text(
                              'Dial Now',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Colors.white),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

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
                        onRemarkChanged: (newRemark) =>
                            setState(() => _remarks[originalIndex] = newRemark),
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
    required ValueChanged<String?> onRemarkChanged,
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

          // Absent / Leave inline Parent Speed Contact Bar & Quick Remarks
          if (status == 1 || status == 3) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: (status == 1 ? AppTheme.red : AppTheme.purple).withOpacity(0.06),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: (status == 1 ? AppTheme.red : AppTheme.purple).withOpacity(0.2),
                  width: 0.8,
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.person_outline_rounded,
                          size: 13,
                          color: status == 1 ? AppTheme.red : AppTheme.purple),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '${student.parentName} • ${student.parentPhone}',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: isDark ? Colors.white70 : AppTheme.dark,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 6),
                      // Direct Speed Call button
                      InkWell(
                        onTap: () => _makePhoneCall(student.parentPhone),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppTheme.green,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.call_rounded, size: 11, color: Colors.white),
                              SizedBox(width: 3),
                              Text(
                                'Call',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      // WhatsApp button
                      InkWell(
                        onTap: () => _openWhatsApp(student.parentPhone, student.name),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF25D366),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.chat_bubble_outline_rounded, size: 11, color: Colors.white),
                              SizedBox(width: 3),
                              Text(
                                'WhatsApp',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  // Quick remark chips
                  Wrap(
                    spacing: 4,
                    runSpacing: 4,
                    children: [
                      'Sick / बिरामी',
                      'Family Event',
                      'Out of Town',
                      'No Answer',
                    ].map((reason) {
                      final isSelected = remark == reason;
                      return InkWell(
                        onTap: () => onRemarkChanged(isSelected ? null : reason),
                        borderRadius: BorderRadius.circular(6),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2.5),
                          decoration: BoxDecoration(
                            color: isSelected
                                ? (status == 1 ? AppTheme.red : AppTheme.purple)
                                : (isDark ? AppTheme.darkCard : Colors.white),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: isSelected
                                  ? Colors.transparent
                                  : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                              width: 0.8,
                            ),
                          ),
                          child: Text(
                            reason,
                            style: TextStyle(
                              fontSize: 9.5,
                              fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                              color: isSelected
                                  ? Colors.white
                                  : (isDark ? Colors.white70 : AppTheme.darkSubtext),
                            ),
                          ),
                        ),
                      );
                    }).toList()..add(
                      InkWell(
                        onTap: onAddRemark,
                        borderRadius: BorderRadius.circular(6),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2.5),
                          decoration: BoxDecoration(
                            color: isDark ? AppTheme.darkCard : Colors.white,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppTheme.primary.withOpacity(0.4), width: 0.8),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.edit_note_rounded, size: 11, color: AppTheme.primary),
                              SizedBox(width: 2),
                              Text(
                                'Custom Note',
                                style: TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.primary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          if (remark != null) ...[
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
              decoration: BoxDecoration(
                color: AppTheme.purple.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
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
                  InkWell(
                    onTap: () => onRemarkChanged(null),
                    child: const Icon(Icons.close_rounded, size: 13, color: AppTheme.purple),
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
        padding: const EdgeInsets.all(8),
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
            const SizedBox(height: 5),
            Text(
              student.name.split(' ').first,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 5),
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
            if (status == 1) ...[
              const SizedBox(height: 4),
              InkWell(
                onTap: () => _makePhoneCall(student.parentPhone),
                borderRadius: BorderRadius.circular(6),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppTheme.green.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.call_rounded, size: 10, color: AppTheme.green),
                      SizedBox(width: 3),
                      Text(
                        'Call',
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w800,
                          color: AppTheme.green,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
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
