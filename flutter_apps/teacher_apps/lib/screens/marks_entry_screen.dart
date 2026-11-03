import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../services/api_service.dart';
import '../providers/teacher_provider.dart';
import '../providers/auth_provider.dart';

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
  bool _isWindowOpen = true; // Marks Submission Window Status (Same as Website)
  bool _isAllSubjectsMode = true; // Mode 1: Master Grade Ledger | Mode 2: Single Subject
  bool? _isSpreadsheetView; // null = auto detect based on screen width
  bool _isInitializing = true; // Fast initial route transition with dedicated loading indicator
  int _currentStep = 0; // 0 = Setup & Scope Screen | 1 = Marks Ledger Sheet Screen

  String _selectedExam = 'First Terminal Examination 2081';
  String? _selectedClass;
  String _selectedSubject = 'all'; // 'all' = All Subjects at Once (Master Grade Ledger)
  String? _lastClassKey;
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();
  final ScrollController _headerHorizontalCtrl = ScrollController();
  final ScrollController _bodyHorizontalCtrl = ScrollController();
  bool _isSyncingHorizontalScroll = false;
  Timer? _recalculateDebounce;

  void _syncHorizontalScroll(ScrollController source, ScrollController target) {
    if (_isSyncingHorizontalScroll) return;
    if (!source.hasClients || !target.hasClients) return;
    final double sourcePixels = source.position.pixels;
    final double targetPixels = target.position.pixels;
    if ((sourcePixels - targetPixels).abs() > 0.5) {
      _isSyncingHorizontalScroll = true;
      try {
        final maxExtent = target.position.maxScrollExtent;
        target.jumpTo(sourcePixels.clamp(0.0, maxExtent));
      } finally {
        _isSyncingHorizontalScroll = false;
      }
    }
  }

  // Standard Curriculum Subjects for Master Ledger (Dynamically loaded per class)
  List<Map<String, dynamic>> _masterSubjects = [
    {'id': 1, 'name': 'English', 'course_name': 'English', 'code': 'ENG', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
    {'id': 2, 'name': 'Nepali', 'course_name': 'Nepali', 'code': 'NEP', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
    {'id': 3, 'name': 'Mathematics', 'course_name': 'Mathematics', 'code': 'MTH', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
    {'id': 4, 'name': 'Science', 'course_name': 'Science', 'code': 'SCI', 'fm': 100.0, 'pm': 40.0, 'has_practical': true, 'th_fm': 75.0, 'pr_fm': 25.0},
    {'id': 5, 'name': 'Social Studies', 'course_name': 'Social Studies', 'code': 'SOC', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
    {'id': 6, 'name': 'Health & PE', 'course_name': 'Health & PE', 'code': 'HPE', 'fm': 50.0, 'pm': 20.0, 'has_practical': true, 'th_fm': 25.0, 'pr_fm': 25.0},
  ];

  int? _loadedClassIdForSubjects;
  bool _isLoadingSubjects = false;
  bool _isLoadingMarks = false;
  bool get _isLoading => _isLoadingSubjects || _isLoadingMarks;

  List<Map<String, dynamic>> _studentsMarks = [];

  String _generateSubjectCode(String name) {
    final words = name.trim().split(RegExp(r'\s+'));
    if (words.length >= 2) {
      final first = words[0].isNotEmpty ? words[0].substring(0, 1) : '';
      final second = words[1].length >= 2 ? words[1].substring(0, 2) : words[1];
      return (first + second).toUpperCase();
    }
    final clean = name.replaceAll(RegExp(r'[^A-Za-z]'), '');
    return (clean.length >= 3 ? clean.substring(0, 3) : (clean.isNotEmpty ? clean : 'SUB')).toUpperCase();
  }

  Map<String, dynamic> _getCurrentSubject() {
    if (_masterSubjects.isEmpty) {
      return {'name': 'General Subject', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0};
    }
    return _masterSubjects.firstWhere(
      (s) => s['name'] == _selectedSubject,
      orElse: () => _masterSubjects.first,
    );
  }

  bool get _currentSubjectHasPractical {
    if (_masterSubjects.isEmpty) return false;
    if (_isAllSubjectsMode) {
      return _masterSubjects.any((s) => s['has_practical'] == true);
    }
    return (_getCurrentSubject()['has_practical'] as bool?) ?? false;
  }

  double _getCurrentSubjectFm() {
    return ((_getCurrentSubject()['fm'] ?? 100.0) as num).toDouble();
  }

  double _getCurrentSubjectPm() {
    return ((_getCurrentSubject()['pm'] ?? 40.0) as num).toDouble();
  }

  double _getCurrentSubjectThFm() {
    final sub = _getCurrentSubject();
    return ((sub['th_fm'] ?? 75.0) as num).toDouble();
  }

  double _getCurrentSubjectPrFm() {
    final sub = _getCurrentSubject();
    return ((sub['pr_fm'] ?? 25.0) as num).toDouble();
  }

  void _syncSingleSubjectControllersFromMaster(String subjectName) {
    final sub = _masterSubjects.firstWhere(
      (s) => s['name'] == subjectName,
      orElse: () => _masterSubjects.isNotEmpty ? _masterSubjects.first : {},
    );
    final hasPr = (sub['has_practical'] as bool?) ?? false;
    final thMax = ((sub['th_fm'] ?? 75.0) as num).toDouble();
    final prMax = ((sub['pr_fm'] ?? 25.0) as num).toDouble();

    for (var entry in _studentsMarks) {
      final controllers = entry['subjectControllers'] as Map<String, TextEditingController>?;
      final thControllers = entry['subjectTheoryControllers'] as Map<String, TextEditingController>?;
      final prControllers = entry['subjectPracticalControllers'] as Map<String, TextEditingController>?;
      final txt = controllers?[subjectName]?.text.trim() ?? '';
      (entry['mark'] as TextEditingController).text = txt;

      final existingTh = thControllers?[subjectName]?.text.trim() ?? '';
      final existingPr = prControllers?[subjectName]?.text.trim() ?? '';

      if (hasPr) {
        if (existingTh.isNotEmpty || existingPr.isNotEmpty) {
          (entry['theory'] as TextEditingController).text = existingTh;
          (entry['practical'] as TextEditingController).text = existingPr;
        } else if (txt.isNotEmpty) {
          final sc = double.tryParse(txt) ?? 0.0;
          final totFm = thMax + prMax > 0 ? (thMax + prMax) : 100.0;
          final pr = sc > prMax ? prMax : (sc > 0 && prMax > 0 ? (sc * (prMax / totFm)).clamp(0, prMax) : 0.0);
          final th = (sc - pr).clamp(0, thMax);
          final thStr = th.toStringAsFixed(th.truncateToDouble() == th ? 0 : 1);
          final prStr = pr > 0 ? pr.toStringAsFixed(pr.truncateToDouble() == pr ? 0 : 1) : '';
          (entry['theory'] as TextEditingController).text = thStr;
          (entry['practical'] as TextEditingController).text = prStr;
          thControllers?[subjectName]?.text = thStr;
          prControllers?[subjectName]?.text = prStr;
        } else {
          (entry['theory'] as TextEditingController).text = '';
          (entry['practical'] as TextEditingController).text = '';
        }
      } else {
        (entry['theory'] as TextEditingController).text = txt;
        (entry['practical'] as TextEditingController).text = '';
        thControllers?[subjectName]?.text = txt;
        prControllers?[subjectName]?.text = '';
      }
    }
  }

  void _syncMasterFromSingleSubject(int index, String subjectName) {
    if (index >= 0 && index < _studentsMarks.length) {
      final s = _studentsMarks[index];
      final sub = _masterSubjects.firstWhere(
        (sub) => sub['name'] == subjectName,
        orElse: () => _masterSubjects.isNotEmpty ? _masterSubjects.first : {},
      );
      final hasPr = (sub['has_practical'] as bool?) ?? false;
      String finalScoreStr = '';

      if (hasPr) {
        final thCtrl = s['theory'] as TextEditingController;
        final prCtrl = s['practical'] as TextEditingController;
        final th = double.tryParse(thCtrl.text.trim()) ?? 0.0;
        final pr = double.tryParse(prCtrl.text.trim()) ?? 0.0;
        final total = th + pr;
        if (thCtrl.text.trim().isNotEmpty || prCtrl.text.trim().isNotEmpty) {
          finalScoreStr = total.toStringAsFixed(total.truncateToDouble() == total ? 0 : 1);
        }
        (s['mark'] as TextEditingController).text = finalScoreStr;

        final thControllers = s['subjectTheoryControllers'] as Map<String, TextEditingController>?;
        final prControllers = s['subjectPracticalControllers'] as Map<String, TextEditingController>?;
        if (thControllers != null && thControllers.containsKey(subjectName)) {
          thControllers[subjectName]?.text = thCtrl.text.trim();
        }
        if (prControllers != null && prControllers.containsKey(subjectName)) {
          prControllers[subjectName]?.text = prCtrl.text.trim();
        }
      } else {
        final markCtrl = s['mark'] as TextEditingController;
        finalScoreStr = markCtrl.text.trim();
        (s['theory'] as TextEditingController).text = finalScoreStr;
        (s['practical'] as TextEditingController).text = '';

        final thControllers = s['subjectTheoryControllers'] as Map<String, TextEditingController>?;
        final prControllers = s['subjectPracticalControllers'] as Map<String, TextEditingController>?;
        if (thControllers != null && thControllers.containsKey(subjectName)) {
          thControllers[subjectName]?.text = finalScoreStr;
        }
        if (prControllers != null && prControllers.containsKey(subjectName)) {
          prControllers[subjectName]?.text = '';
        }
      }

      final controllers = s['subjectControllers'] as Map<String, TextEditingController>?;
      if (controllers != null && controllers.containsKey(subjectName)) {
        controllers[subjectName]?.text = finalScoreStr;
      }
    }
  }

  void _rebuildSubjectControllersForStudents() {
    for (var s in _studentsMarks) {
      final controllers = s['subjectControllers'] as Map<String, TextEditingController>? ?? <String, TextEditingController>{};
      final thControllers = s['subjectTheoryControllers'] as Map<String, TextEditingController>? ?? <String, TextEditingController>{};
      final prControllers = s['subjectPracticalControllers'] as Map<String, TextEditingController>? ?? <String, TextEditingController>{};
      for (var sub in _masterSubjects) {
        final subName = sub['name'] as String;
        if (!controllers.containsKey(subName)) {
          controllers[subName] = TextEditingController(text: '');
        }
        if (!thControllers.containsKey(subName)) {
          thControllers[subName] = TextEditingController(text: '');
        }
        if (!prControllers.containsKey(subName)) {
          prControllers[subName] = TextEditingController(text: '');
        }
      }
      s['subjectControllers'] = controllers;
      s['subjectTheoryControllers'] = thControllers;
      s['subjectPracticalControllers'] = prControllers;
    }
    _recalculateAll();
  }

  List<Map<String, dynamic>> _getStandardSubjectsForGrade(String className, int classId) {
    final lower = className.toLowerCase();
    if (lower.contains('11') || lower.contains('12')) {
      return [
        {'id': 101, 'name': 'Compulsory English', 'course_name': 'Compulsory English', 'code': 'ENG', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
        {'id': 102, 'name': 'Compulsory Nepali', 'course_name': 'Compulsory Nepali', 'code': 'NEP', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
        {'id': 103, 'name': 'Social Studies & Life Skills', 'course_name': 'Social Studies & Life Skills', 'code': 'SOC', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
        {'id': 104, 'name': 'Physics / Accountancy', 'course_name': 'Physics / Accountancy', 'code': 'PHY', 'fm': 100.0, 'pm': 40.0, 'has_practical': true, 'th_fm': 75.0, 'pr_fm': 25.0},
        {'id': 105, 'name': 'Chemistry / Economics', 'course_name': 'Chemistry / Economics', 'code': 'CHE', 'fm': 100.0, 'pm': 40.0, 'has_practical': true, 'th_fm': 75.0, 'pr_fm': 25.0},
        {'id': 106, 'name': 'Mathematics / Biology / CS', 'course_name': 'Mathematics / Biology / CS', 'code': 'MTH', 'fm': 100.0, 'pm': 40.0, 'has_practical': true, 'th_fm': 75.0, 'pr_fm': 25.0},
      ];
    }
    // Standard Curriculum for Secondary & Basic (Grades 1-10)
    return [
      {'id': 1, 'name': 'Compulsory English', 'course_name': 'Compulsory English', 'code': 'ENG', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
      {'id': 2, 'name': 'Compulsory Nepali', 'course_name': 'Compulsory Nepali', 'code': 'NEP', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
      {'id': 3, 'name': 'Compulsory Mathematics', 'course_name': 'Compulsory Mathematics', 'code': 'MTH', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
      {'id': 4, 'name': 'Science & Technology', 'course_name': 'Science & Technology', 'code': 'SCI', 'fm': 100.0, 'pm': 40.0, 'has_practical': true, 'th_fm': 75.0, 'pr_fm': 25.0},
      {'id': 5, 'name': 'Social Studies', 'course_name': 'Social Studies', 'code': 'SOC', 'fm': 100.0, 'pm': 40.0, 'has_practical': false, 'th_fm': 75.0, 'pr_fm': 25.0},
      {'id': 6, 'name': 'Health & Physical Education', 'course_name': 'Health & Physical Education', 'code': 'HPE', 'fm': 50.0, 'pm': 20.0, 'has_practical': true, 'th_fm': 25.0, 'pr_fm': 25.0},
    ];
  }

  Future<void> _fetchClassSubjects(int classId, List<TeacherClass> classObjList, String selectedSection) async {
    if (_loadedClassIdForSubjects == classId) return;
    _loadedClassIdForSubjects = classId;
    if (mounted) setState(() => _isLoadingSubjects = true);

    try {
      // Check if user is Class In-Charge of this selected section
      final matchingClasses = classObjList.where((c) => c.section == selectedSection).toList();
      final bool isClassIncharge = matchingClasses.any((c) => c.isClassTeacher);

      List<Map<String, dynamic>> fetchedSubjects = [];

      // 1. Primary: fetch from dedicated API endpoint /classes/{id}/subjects
      try {
        final res = await _apiService.getClassSubjects(classId);
        if (res.success && res.data != null && res.data is List && (res.data as List).isNotEmpty) {
          final list = res.data as List;
          for (var item in list) {
            if (item is Map) {
              final id = item['id'] is int ? item['id'] as int : int.tryParse(item['id']?.toString() ?? '') ?? 0;
              final name = item['name']?.toString() ?? item['course_name']?.toString() ?? 'Subject';
              final code = item['code']?.toString() ?? _generateSubjectCode(name);
              final fm = ((item['fm'] ?? item['total_marks'] ?? 100) as num).toDouble();
              final pm = ((item['pm'] ?? item['pass_marks'] ?? 40) as num).toDouble();
              final hasPr = item['has_practical'] == true ||
                  RegExp(r'(science|sci|physic|chem|bio|comput|hpe|health|vocat|art)', caseSensitive: false).hasMatch(name);
              final thFm = ((item['th_fm'] ?? (hasPr ? (fm > 25 ? fm - 25 : fm) : fm)) as num).toDouble();
              final prFm = ((item['pr_fm'] ?? (hasPr ? (fm > 25 ? 25 : 0) : 0)) as num).toDouble();
              fetchedSubjects.add({
                'id': id,
                'name': name,
                'course_name': name,
                'code': code,
                'fm': fm,
                'pm': pm,
                'has_practical': hasPr,
                'th_fm': thFm,
                'pr_fm': prFm,
                'is_assigned': item['is_assigned'] == true,
              });
            }
          }
        }
      } catch (_) {}

      // 2. Secondary: fetch class details
      if (fetchedSubjects.length <= 1) {
        try {
          final res = await _apiService.getClassDetail(classId.toString());
          if (res.success && res.data != null && res.data!['class'] != null) {
            final clsData = res.data!['class'];
            if (clsData['courses'] is List && (clsData['courses'] as List).isNotEmpty) {
              final courses = clsData['courses'] as List;
              final List<Map<String, dynamic>> apiCourses = [];
              for (var c in courses) {
                if (c is Map) {
                  final id = c['id'] is int ? c['id'] as int : int.tryParse(c['id']?.toString() ?? '') ?? 0;
                  final name = c['course_name']?.toString() ?? c['name']?.toString() ?? 'Subject';
                  final hasPr = RegExp(r'(science|sci|physic|chem|bio|comput|hpe|health|vocat|art)', caseSensitive: false).hasMatch(name);
                  apiCourses.add({
                    'id': id,
                    'name': name,
                    'course_name': name,
                    'code': _generateSubjectCode(name),
                    'fm': 100.0,
                    'pm': 40.0,
                    'has_practical': hasPr,
                    'th_fm': hasPr ? 75.0 : 100.0,
                    'pr_fm': hasPr ? 25.0 : 0.0,
                    'is_assigned': matchingClasses.any((mc) => mc.subject.toLowerCase() == name.toLowerCase()),
                  });
                }
              }
              if (apiCourses.length > fetchedSubjects.length) {
                fetchedSubjects = apiCourses;
              }
            }
          }
        } catch (_) {}
      }

      // 3. For Class In-Charge: Ensure FULL class curriculum is present!
      if (isClassIncharge) {
        if (fetchedSubjects.length <= 1) {
          // Fallback to standard grade curriculum so Class In-Charge has ALL subjects
          final standardSubjects = _getStandardSubjectsForGrade(selectedSection, classId);
          // Map any existing assigned course ID into standard subjects if matching
          for (var mc in matchingClasses) {
            if (mc.subject.isNotEmpty && mc.courseId != null) {
              for (var ss in standardSubjects) {
                if (ss['name'].toString().toLowerCase().contains(mc.subject.toLowerCase()) ||
                    mc.subject.toLowerCase().contains(ss['name'].toString().toLowerCase())) {
                  ss['id'] = mc.courseId;
                  ss['name'] = mc.subject;
                  ss['course_name'] = mc.subject;
                  ss['is_assigned'] = true;
                }
              }
            }
          }
          fetchedSubjects = standardSubjects;
        }
      } else {
        // For Regular Subject Teacher:
        // Restrict strictly to the teacher's assigned subjects in this class!
        final assignedOnly = <Map<String, dynamic>>[];
        if (matchingClasses.isNotEmpty) {
          for (var mc in matchingClasses) {
            if (mc.subject.isNotEmpty) {
              final hasPr = RegExp(r'(science|sci|physic|chem|bio|comput|hpe|health|vocat|art)', caseSensitive: false).hasMatch(mc.subject);
              assignedOnly.add({
                'id': mc.courseId ?? mc.id ?? 1,
                'name': mc.subject,
                'course_name': mc.subject,
                'code': _generateSubjectCode(mc.subject),
                'fm': 100.0,
                'pm': 40.0,
                'has_practical': hasPr,
                'th_fm': hasPr ? 75.0 : 100.0,
                'pr_fm': hasPr ? 25.0 : 0.0,
                'is_assigned': true,
              });
            }
          }
        }
        if (assignedOnly.isNotEmpty) {
          fetchedSubjects = assignedOnly;
        } else if (fetchedSubjects.isEmpty) {
          fetchedSubjects = [
            {
              'id': 1,
              'name': 'Assigned Subject',
              'course_name': 'Assigned Subject',
              'code': 'SUB',
              'fm': 100.0,
              'pm': 40.0,
              'has_practical': false,
              'th_fm': 75.0,
              'pr_fm': 25.0,
              'is_assigned': true,
            }
          ];
        }
      }

      if (fetchedSubjects.isNotEmpty && mounted) {
        setState(() {
          _masterSubjects = fetchedSubjects;

          if (isClassIncharge) {
            // Class in-charge can use 'all' or choose individual subject
            if (_selectedSubject != 'all' && !_masterSubjects.any((s) => s['name'] == _selectedSubject)) {
              _selectedSubject = 'all';
              _isAllSubjectsMode = true;
            }
          } else {
            // Regular teacher: must be single subject mode on their assigned subject
            _isAllSubjectsMode = false;
            final availableNames = _masterSubjects.map((s) => s['name'] as String).toList();
            if (!availableNames.contains(_selectedSubject)) {
              _selectedSubject = availableNames.isNotEmpty ? availableNames.first : 'General';
            }
          }

          _rebuildSubjectControllersForStudents();
        });

        // Load marks for this class and its real subjects
        final classObj = classObjList.firstWhere(
          (c) => (c.classId == classId || c.id == classId),
          orElse: () => classObjList.first,
        );
        _loadExistingMarks(classId, classObj.sectionId, classObj.courseId);
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _isLoadingSubjects = false);
    }
  }

  @override
  void initState() {
    super.initState();
    _bodyHorizontalCtrl.addListener(() {
      _syncHorizontalScroll(_bodyHorizontalCtrl, _headerHorizontalCtrl);
    });
    _headerHorizontalCtrl.addListener(() {
      _syncHorizontalScroll(_headerHorizontalCtrl, _bodyHorizontalCtrl);
    });
    if (widget.initialExam != null) _selectedExam = widget.initialExam!;
    if (widget.initialClass != null) _selectedClass = widget.initialClass!;
    if (widget.initialSubject != null && widget.initialSubject != 'all') {
      _selectedSubject = widget.initialSubject!;
      _isAllSubjectsMode = false;
    }
    // Defer heavy allocations to after the first frame so dashboard navigation is 0-delay instant
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initialSetup();
    });
  }

  void _initialSetup() {
    if (!mounted) return;
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final classSections = classesList.map((c) => c.section).toSet().toList();
    if (classSections.isEmpty) classSections.add('Grade 10 - A');

    if (_selectedClass == null || !classSections.contains(_selectedClass)) {
      _selectedClass = classSections.first;
    }

    final classObj = classesList.firstWhere(
      (c) => c.section == _selectedClass,
      orElse: () => classesList.isNotEmpty ? classesList.first : AppData.classes.first,
    );

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final allStudents = teacherProvider.studentList;
    final classStudents = allStudents.where((s) {
      if (classObj.classId != null && s.classId != null && s.classId != classObj.classId) return false;
      if (classObj.sectionId != null && s.sectionId != null && s.sectionId != classObj.sectionId) return false;
      return true;
    }).toList();

    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;

    // Determine if logged-in teacher is Class In-Charge (कक्षा शिक्षक) of the selected class
    final matchingClassEntries = classesList.where((c) => c.section == _selectedClass).toList();
    final bool isClassIncharge = matchingClassEntries.any((c) => c.isClassTeacher) ||
        authProvider.isAdminRole ||
        (teacherProvider.classes.any((cls) {
          if (cls is Map) {
            final sec = cls['section']?.toString() ??
                (cls['class_name'] != null ? '${cls['class_name']} — ${cls['section_name'] ?? 'A'}' : '');
            return (sec == _selectedClass || cls['section'] == _selectedClass) &&
                (cls['is_class_teacher'] == true || cls['is_class_teacher'] == 1 || cls['is_class_teacher'] == '1');
          }
          return false;
        }));

    if (!isClassIncharge) {
      _isAllSubjectsMode = false;
      final availableNames = _masterSubjects.map((s) => s['name'] as String).toList();
      _selectedSubject = availableNames.isNotEmpty ? availableNames.first : 'General';
    }

    final targetClassId = classObj.classId ?? classObj.id ?? 1;
    final currentKey = '${targetClassId}_${classObj.sectionId ?? 1}_${activeStudents.length}';
    _lastClassKey = currentKey;
    _populateStudents(activeStudents);

    setState(() {
      _isInitializing = false;
    });

    _loadExistingMarks(targetClassId, classObj.sectionId, classObj.courseId);
    _fetchClassSubjects(targetClassId, classesList, _selectedClass!);
  }

  @override
  void dispose() {
    _headerHorizontalCtrl.dispose();
    _bodyHorizontalCtrl.dispose();
    _recalculateDebounce?.cancel();
    _searchCtrl.dispose();
    for (var s in _studentsMarks) {
      (s['mark'] as TextEditingController?)?.dispose();
      (s['theory'] as TextEditingController?)?.dispose();
      (s['practical'] as TextEditingController?)?.dispose();
      final subs = s['subjectControllers'] as Map<String, TextEditingController>?;
      subs?.forEach((_, ctrl) => ctrl.dispose());
      final thSubs = s['subjectTheoryControllers'] as Map<String, TextEditingController>?;
      thSubs?.forEach((_, ctrl) => ctrl.dispose());
      final prSubs = s['subjectPracticalControllers'] as Map<String, TextEditingController>?;
      prSubs?.forEach((_, ctrl) => ctrl.dispose());
    }
    super.dispose();
  }

  void _populateStudents(List<Student> students) {
    _studentsMarks = students.asMap().entries.map((e) {
      final s = e.value;
      final subjectControllers = <String, TextEditingController>{};
      final subjectTheoryControllers = <String, TextEditingController>{};
      final subjectPracticalControllers = <String, TextEditingController>{};
      for (var sub in _masterSubjects) {
        final subName = sub['name'] as String;
        subjectControllers[subName] = TextEditingController(text: '');
        subjectTheoryControllers[subName] = TextEditingController(text: '');
        subjectPracticalControllers[subName] = TextEditingController(text: '');
      }

      return {
        'student_id': s.id ?? (e.key + 1),
        'roll_no': s.rollNo,
        'name': s.name,
        'avatar': s.avatar,
        'subjectControllers': subjectControllers,
        'subjectTheoryControllers': subjectTheoryControllers,
        'subjectPracticalControllers': subjectPracticalControllers,
        'mark': TextEditingController(text: ''),
        'theory': TextEditingController(text: ''),
        'practical': TextEditingController(text: ''),
        'total': 0.0,
        'totalMax': 100.0,
        'percentage': 0.0,
        'grade': '—',
        'gpa': '—',
        'anyFailed': false,
      };
    }).toList();
  }

  Future<void> _loadExistingMarks(int classId, int? sectionId, int? courseId) async {
    if (mounted) setState(() => _isLoadingMarks = true);

    // 1. Immediately restore local cached marks if available (Fast 0-delay display)
    try {
      final prefs = await SharedPreferences.getInstance();
      final cacheKey = 'nsh_cached_marks_${classId}_$_selectedExam';
      final cachedJson = prefs.getString(cacheKey);
      if (cachedJson != null && cachedJson.isNotEmpty) {
        final decoded = jsonDecode(cachedJson);
        if (decoded is List && mounted) {
          _applyMarksToStudentControllers(decoded);
        }
      }
    } catch (_) {}

    // 2. Fetch official server marks from live database
    try {
      final res = await _apiService.getExamMarks(
        classId: classId,
        sectionId: sectionId,
        courseId: _isAllSubjectsMode ? null : courseId,
        examId: _selectedExam,
      );
      if (res.success && res.data != null && mounted) {
        List existing = [];
        if (res.data is List) {
          existing = res.data as List;
        } else if (res.data is Map && (res.data as Map)['marks'] is List) {
          existing = (res.data as Map)['marks'] as List;
        }

        if (existing.isNotEmpty) {
          _applyMarksToStudentControllers(existing);
          // Persist to local cache for instant future loads
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('nsh_cached_marks_${classId}_$_selectedExam', jsonEncode(existing));
        }
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _isLoadingMarks = false);
    }
  }

  void _applyMarksToStudentControllers(List existing) {
    final markMap = <int, Map<dynamic, double>>{};
    final thMap = <int, Map<dynamic, double>>{};
    final prMap = <int, Map<dynamic, double>>{};

    for (var item in existing) {
      if (item is Map && item['student_id'] != null) {
        final sId = item['student_id'] is int
            ? item['student_id'] as int
            : int.tryParse(item['student_id'].toString()) ?? 0;
        final sc = ((item['score'] ?? item['marks'] ?? 0) as num).toDouble();
        final cId = item['course_id'];
        final cName = item['course_name']?.toString().toLowerCase().trim();

        double? th = item['theory'] != null ? ((item['theory'] as num).toDouble()) : null;
        double? pr = item['practical'] != null ? ((item['practical'] as num).toDouble()) : null;

        if (!markMap.containsKey(sId)) markMap[sId] = {};
        if (!thMap.containsKey(sId)) thMap[sId] = {};
        if (!prMap.containsKey(sId)) prMap[sId] = {};

        if (cId != null) {
          markMap[sId]![cId.toString()] = sc;
          markMap[sId]![cId] = sc;
          if (th != null) {
            thMap[sId]![cId.toString()] = th;
            thMap[sId]![cId] = th;
          }
          if (pr != null) {
            prMap[sId]![cId.toString()] = pr;
            prMap[sId]![cId] = pr;
          }
        }
        if (cName != null && cName.isNotEmpty) {
          markMap[sId]![cName] = sc;
          if (th != null) thMap[sId]![cName] = th;
          if (pr != null) prMap[sId]![cName] = pr;
        }
      }
    }

    if (markMap.isNotEmpty && mounted) {
      setState(() {
        for (var entry in _studentsMarks) {
          final sId = entry['student_id'] as int;
          if (markMap.containsKey(sId)) {
            final studentCourseScores = markMap[sId]!;
            final studentThScores = thMap[sId] ?? {};
            final studentPrScores = prMap[sId] ?? {};

            final controllers = entry['subjectControllers'] as Map<String, TextEditingController>?;
            final thControllers = entry['subjectTheoryControllers'] as Map<String, TextEditingController>?;
            final prControllers = entry['subjectPracticalControllers'] as Map<String, TextEditingController>?;

            for (var sub in _masterSubjects) {
              final sName = sub['name'] as String;
              final sNameLower = sName.toLowerCase().trim();
              final cId = sub['id'];
              final hasPr = (sub['has_practical'] as bool?) ?? false;
              final thFm = ((sub['th_fm'] ?? (hasPr ? 75.0 : 100.0)) as num).toDouble();
              final prFm = ((sub['pr_fm'] ?? (hasPr ? 25.0 : 0.0)) as num).toDouble();

              double? foundScore = studentCourseScores[cId] ??
                  studentCourseScores[cId?.toString()] ??
                  studentCourseScores[sNameLower];

              if (foundScore != null) {
                controllers?[sName]?.text = foundScore.toStringAsFixed(foundScore.truncateToDouble() == foundScore ? 0 : 1);

                double? foundTh = studentThScores[cId] ??
                    studentThScores[cId?.toString()] ??
                    studentThScores[sNameLower];
                double? foundPr = studentPrScores[cId] ??
                    studentPrScores[cId?.toString()] ??
                    studentPrScores[sNameLower];

                if (hasPr) {
                  if (foundTh == null || foundPr == null) {
                    final totFm = thFm + prFm > 0 ? (thFm + prFm) : 100.0;
                    foundPr = foundScore > prFm ? prFm : (foundScore > 0 && prFm > 0 ? (foundScore * (prFm / totFm)).clamp(0, prFm) : 0.0);
                    foundTh = (foundScore - foundPr).clamp(0, thFm);
                  }
                  thControllers?[sName]?.text = foundTh.toStringAsFixed(foundTh.truncateToDouble() == foundTh ? 0 : 1);
                  prControllers?[sName]?.text = foundPr > 0 ? foundPr.toStringAsFixed(foundPr.truncateToDouble() == foundPr ? 0 : 1) : '';
                } else {
                  thControllers?[sName]?.text = foundScore.toStringAsFixed(foundScore.truncateToDouble() == foundScore ? 0 : 1);
                  prControllers?[sName]?.text = '';
                }
              }
            }

            // Sync single subject mode controllers if currently viewing single subject
            if (!_isAllSubjectsMode) {
              _syncSingleSubjectControllersFromMaster(_selectedSubject);
            }
          }
        }
        _recalculateAll();
      });
    }
  }

  // --- Nepal CDC Standard Grading Engine ---
  String _calculateCdcGrade(num score, {double maxMarks = 100.0}) {
    final percentage = maxMarks > 0 ? (score / maxMarks) * 100.0 : score.toDouble();
    if (percentage >= 90) return 'A+';
    if (percentage >= 80) return 'A';
    if (percentage >= 70) return 'B+';
    if (percentage >= 60) return 'B';
    if (percentage >= 50) return 'C+';
    if (percentage >= 40) return 'C';
    if (percentage >= 35) return 'D';
    return 'NG';
  }

  String _calculateCdcGpa(num percentage) {
    if (percentage >= 90) return '4.00';
    if (percentage >= 80) return '3.60';
    if (percentage >= 70) return '3.20';
    if (percentage >= 60) return '2.80';
    if (percentage >= 50) return '2.40';
    if (percentage >= 40) return '2.00';
    if (percentage >= 35) return '1.60';
    return '0.00';
  }

  Color _getGradeColor(String grade) {
    final clean = grade.replaceAll('*', '');
    if (clean == 'A+') return const Color(0xFF10B981);
    if (clean == 'A') return const Color(0xFF0D9488);
    if (clean == 'B+') return const Color(0xFF2563EB);
    if (clean == 'B') return const Color(0xFF0284C7);
    if (clean == 'C+') return const Color(0xFFD97706);
    if (clean == 'C') return const Color(0xFFEA580C);
    if (clean == 'D') return const Color(0xFF4B5563);
    return const Color(0xFFDC2626);
  }

  void _recalculateRow(int index, {bool immediate = false}) {
    if (index < 0 || index >= _studentsMarks.length) return;
    final s = _studentsMarks[index];
    if (_isAllSubjectsMode) {
      final controllers = s['subjectControllers'] as Map<String, TextEditingController>? ?? {};
      double total = 0;
      double maxTotal = 0;
      bool hasAny = false;
      bool anyFailed = false;

      for (var sub in _masterSubjects) {
        final sName = sub['name'] as String;
        final fm = (sub['fm'] as num).toDouble();
        final pm = (sub['pm'] as num).toDouble();
        maxTotal += fm;

        final ctrl = controllers[sName];
        final thCtrl = (s['subjectTheoryControllers'] as Map<String, TextEditingController>?)?[sName];
        final prCtrl = (s['subjectPracticalControllers'] as Map<String, TextEditingController>?)?[sName];
        final hasPr = (sub['has_practical'] as bool?) ?? false;
        final thFm = ((sub['th_fm'] ?? (hasPr ? 75.0 : fm)) as num).toDouble();
        final prFm = ((sub['pr_fm'] ?? (hasPr ? 25.0 : 0.0)) as num).toDouble();

        if (ctrl != null && ctrl.text.trim().isNotEmpty) {
          final val = double.tryParse(ctrl.text.trim());
          if (val != null) {
            hasAny = true;
            double effectiveVal = val;
            if (val > fm) {
              ctrl.text = fm.toStringAsFixed(0);
              total += fm;
              effectiveVal = fm;
            } else if (val < 0) {
              ctrl.text = '0';
              effectiveVal = 0;
            } else {
              total += val;
            }

            if (hasPr) {
              final totFm = thFm + prFm > 0 ? (thFm + prFm) : 100.0;
              final pr = effectiveVal > prFm ? prFm : (effectiveVal > 0 && prFm > 0 ? (effectiveVal * (prFm / totFm)).clamp(0, prFm) : 0.0);
              final th = (effectiveVal - pr).clamp(0, thFm);
              thCtrl?.text = th.toStringAsFixed(th.truncateToDouble() == th ? 0 : 1);
              prCtrl?.text = pr > 0 ? pr.toStringAsFixed(pr.truncateToDouble() == pr ? 0 : 1) : '';
            } else {
              thCtrl?.text = effectiveVal.toStringAsFixed(effectiveVal.truncateToDouble() == effectiveVal ? 0 : 1);
              prCtrl?.text = '';
            }

            if (val < pm) {
              anyFailed = true;
            }
          }
        } else {
          thCtrl?.text = '';
          prCtrl?.text = '';
        }
      }

      if (hasAny && maxTotal > 0) {
        final pct = (total / maxTotal) * 100;
        s['total'] = total;
        s['totalMax'] = maxTotal;
        s['percentage'] = pct;
        s['gpa'] = _calculateCdcGpa(pct);
        final baseGrade = _calculateCdcGrade(pct);
        s['grade'] = anyFailed && baseGrade != 'NG' ? '$baseGrade*' : baseGrade;
        s['anyFailed'] = anyFailed;
      } else {
        s['total'] = 0.0;
        s['totalMax'] = maxTotal;
        s['percentage'] = 0.0;
        s['gpa'] = '—';
        s['grade'] = '—';
        s['anyFailed'] = false;
      }
    } else {
      final sub = _getCurrentSubject();
      final hasPr = (sub['has_practical'] as bool?) ?? false;
      final fullMax = _getCurrentSubjectFm();
      final passMarks = _getCurrentSubjectPm();

      double total = 0.0;
      bool failed = false;

      if (hasPr) {
        final thCtrl = s['theory'] as TextEditingController;
        final prCtrl = s['practical'] as TextEditingController;
        double th = double.tryParse(thCtrl.text.trim()) ?? 0;
        double pr = double.tryParse(prCtrl.text.trim()) ?? 0;
        final thMax = _getCurrentSubjectThFm();
        final prMax = _getCurrentSubjectPrFm();

        if (th > thMax) {
          th = thMax;
          thCtrl.text = thMax.toStringAsFixed(thMax.truncateToDouble() == thMax ? 0 : 1);
        }
        if (pr > prMax) {
          pr = prMax;
          prCtrl.text = prMax.toStringAsFixed(prMax.truncateToDouble() == prMax ? 0 : 1);
        }
        total = (th + pr).clamp(0, fullMax).toDouble();
        failed = total < passMarks;
      } else {
        final markCtrl = s['mark'] as TextEditingController;
        double mark = double.tryParse(markCtrl.text.trim()) ?? 0;
        if (mark > fullMax) {
          mark = fullMax;
          markCtrl.text = fullMax.toStringAsFixed(fullMax.truncateToDouble() == fullMax ? 0 : 1);
        }
        total = mark;
        failed = mark < passMarks;
      }

      _syncMasterFromSingleSubject(index, _selectedSubject);

      s['total'] = total;
      s['totalMax'] = fullMax > 0 ? fullMax : 100.0;
      final pct = fullMax > 0 ? (total / fullMax) * 100 : total;
      s['percentage'] = pct;
      s['gpa'] = _calculateCdcGpa(pct);
      final base = _calculateCdcGrade(total, maxMarks: fullMax);
      s['grade'] = failed && base != 'NG' ? '$base*' : base;
      s['anyFailed'] = failed;
    }

    if (immediate) {
      _recalculateDebounce?.cancel();
      if (mounted) setState(() {});
    } else {
      _recalculateDebounce?.cancel();
      _recalculateDebounce = Timer(const Duration(milliseconds: 100), () {
        if (mounted) setState(() {});
      });
    }
  }

  void _recalculateAll() {
    for (int i = 0; i < _studentsMarks.length; i++) {
      _recalculateRow(i, immediate: false);
    }
    if (mounted) setState(() {});
  }

  // --- Website Action 1: Set Empty to 0 (Absent) ---
  void _fillZeroForEmpty() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: const Row(
          children: [
            Icon(Icons.auto_fix_high_rounded, color: AppTheme.primary, size: 22),
            SizedBox(width: 10),
            Text('Set Empty to 0 (Absent)', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
          ],
        ),
        content: const Text(
          'Fill 0 (Absent) for all currently blank mark inputs in this sheet? Students who were absent during the examination will be recorded with 0 points.',
          style: TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() {
                for (var s in _studentsMarks) {
                  if (_isAllSubjectsMode) {
                    final controllers = s['subjectControllers'] as Map<String, TextEditingController>;
                    for (var c in controllers.values) {
                      if (c.text.trim().isEmpty) c.text = '0';
                    }
                  } else {
                    final m = s['mark'] as TextEditingController;
                    if (m.text.trim().isEmpty) m.text = '0';
                  }
                }
                _recalculateAll();
              });
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('All blank inputs filled with 0 (Absent) ✅'),
                  backgroundColor: AppTheme.primary,
                  duration: Duration(seconds: 1),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Confirm Set 0'),
          ),
        ],
      ),
    );
  }

  // --- Action 2: Autofill Practicals (CAS) ---
  void _autoFillFullPracticals() {
    setState(() {
      for (var s in _studentsMarks) {
        if (!_isAllSubjectsMode) {
          final prMax = _getCurrentSubjectPrFm();
          (s['practical'] as TextEditingController).text = prMax > 0 ? prMax.toStringAsFixed(0) : '0';
          final th = double.tryParse((s['theory'] as TextEditingController).text.trim()) ?? 0.0;
          final total = th + prMax;
          (s['mark'] as TextEditingController).text = total > 0 ? total.toStringAsFixed(0) : '';
        } else {
          for (var sub in _masterSubjects) {
            if ((sub['has_practical'] as bool?) == true) {
              final sName = sub['name'] as String;
              final prMax = ((sub['pr_fm'] ?? 25.0) as num).toDouble();
              final controllers = s['subjectControllers'] as Map<String, TextEditingController>;
              if (controllers[sName]?.text.trim().isEmpty ?? true) {
                controllers[sName]?.text = prMax > 0 ? prMax.toStringAsFixed(0) : '0';
              }
            }
          }
        }
      }
      _recalculateAll();
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Auto-filled internal evaluation practical marks (CAS) ✅'),
        backgroundColor: AppTheme.primary,
        duration: Duration(seconds: 1),
      ),
    );
  }

  // --- Action 3: Set Practical Evaluation Settings Modal ---
  void _openPracticalSettingsModal({Map<String, dynamic>? subjectOverride}) {
    final targetSub = subjectOverride ?? _getCurrentSubject();
    final bool isSingle = !_isAllSubjectsMode || subjectOverride != null;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    bool tempHasPr = (targetSub['has_practical'] as bool?) ?? false;
    double tempThFm = ((targetSub['th_fm'] ?? 75.0) as num).toDouble();
    double tempPrFm = ((targetSub['pr_fm'] ?? 25.0) as num).toDouble();
    final TextEditingController thCtrl = TextEditingController(text: tempThFm.toStringAsFixed(0));
    final TextEditingController prCtrl = TextEditingController(text: tempPrFm.toStringAsFixed(0));

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (modalCtx, setModalState) {
          return Container(
            padding: EdgeInsets.fromLTRB(20, 16, 20, MediaQuery.of(ctx).viewInsets.bottom + 24),
            decoration: BoxDecoration(
              color: isDark ? AppTheme.darkCard : Colors.white,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              boxShadow: [
                BoxShadow(color: Colors.black.withOpacity(0.25), blurRadius: 20, offset: const Offset(0, -4)),
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
                      decoration: BoxDecoration(
                        color: Colors.grey.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.science_rounded, color: AppTheme.primary, size: 22),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isSingle
                                  ? 'Practical Evaluation: ${targetSub['name']}'
                                  : 'Curriculum Practical Settings',
                              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
                            ),
                            const Text(
                              'Nepal CDC / School Management Mark System',
                              style: TextStyle(fontSize: 11, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
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

                  if (!isSingle) ...[
                    const Text(
                      'Toggle Practical for Each Subject:',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 8),
                    ..._masterSubjects.map((sub) {
                      final bool sHasPr = (sub['has_practical'] as bool?) ?? false;
                      final sTh = (sub['th_fm'] ?? 75).toInt();
                      final sPr = (sub['pr_fm'] ?? 25).toInt();
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkBg : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: sHasPr ? AppTheme.primary.withOpacity(0.4) : AppTheme.cardBorder),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              sHasPr ? Icons.science_rounded : Icons.menu_book_rounded,
                              size: 18,
                              color: sHasPr ? AppTheme.primary : AppTheme.grey,
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(sub['name'] as String, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5)),
                                  Text(
                                    sHasPr ? 'Practical: YES ($sTh TH + $sPr PR)' : 'Practical: NO (100% Theory)',
                                    style: TextStyle(fontSize: 10.5, color: sHasPr ? AppTheme.primary : AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              ),
                            ),
                            Switch.adaptive(
                              value: sHasPr,
                              activeColor: AppTheme.primary,
                              onChanged: (val) {
                                setModalState(() {
                                  sub['has_practical'] = val;
                                  if (val) {
                                    sub['th_fm'] = sub['th_fm'] ?? 75.0;
                                    sub['pr_fm'] = sub['pr_fm'] ?? 25.0;
                                  }
                                });
                                setState(() {
                                  sub['has_practical'] = val;
                                });
                              },
                            ),
                          ],
                        ),
                      );
                    }),
                    const SizedBox(height: 12),
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark ? AppTheme.darkBg : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: tempHasPr ? AppTheme.primary.withOpacity(0.4) : AppTheme.cardBorder),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            tempHasPr ? Icons.science_rounded : Icons.science_outlined,
                            size: 22,
                            color: tempHasPr ? AppTheme.primary : AppTheme.grey,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Does this subject have Practical / CAS?',
                                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                                ),
                                Text(
                                  tempHasPr
                                      ? 'YES: Students evaluated on Theory + Practical'
                                      : 'NO: 100% Theory exam only (No practical marks)',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: tempHasPr ? AppTheme.primary : AppTheme.darkSubtext,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Switch.adaptive(
                            value: tempHasPr,
                            activeColor: AppTheme.primary,
                            onChanged: (val) {
                              setModalState(() {
                                tempHasPr = val;
                              });
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    if (tempHasPr) ...[
                      const Text(
                        'Select Standard Curriculum Ratio:',
                        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        children: [
                          ChoiceChip(
                            label: const Text('CDC Standard (75 TH + 25 PR)'),
                            selected: tempThFm == 75.0 && tempPrFm == 25.0,
                            onSelected: (sel) {
                              if (sel) {
                                setModalState(() {
                                  tempThFm = 75.0;
                                  tempPrFm = 25.0;
                                  thCtrl.text = '75';
                                  prCtrl.text = '25';
                                });
                              }
                            },
                          ),
                          ChoiceChip(
                            label: const Text('Equal Split (50 TH + 50 PR)'),
                            selected: tempThFm == 50.0 && tempPrFm == 50.0,
                            onSelected: (sel) {
                              if (sel) {
                                setModalState(() {
                                  tempThFm = 50.0;
                                  tempPrFm = 50.0;
                                  thCtrl.text = '50';
                                  prCtrl.text = '50';
                                });
                              }
                            },
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Theory Full Marks (TH)', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                                const SizedBox(height: 4),
                                TextField(
                                  controller: thCtrl,
                                  keyboardType: TextInputType.number,
                                  decoration: InputDecoration(
                                    isDense: true,
                                    hintText: '75',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  onChanged: (v) {
                                    tempThFm = double.tryParse(v) ?? 75.0;
                                  },
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Practical Full Marks (PR)', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                                const SizedBox(height: 4),
                                TextField(
                                  controller: prCtrl,
                                  keyboardType: TextInputType.number,
                                  decoration: InputDecoration(
                                    isDense: true,
                                    hintText: '25',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  onChanged: (v) {
                                    tempPrFm = double.tryParse(v) ?? 25.0;
                                  },
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Total Full Marks = ${(tempThFm + tempPrFm).toInt()} (Pass Marks: ${(targetSub['pm'] ?? 40).toInt()})',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
                      ),
                    ],
                  ],

                  const SizedBox(height: 18),
                  SizedBox(
                    width: double.infinity,
                    height: 44,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () {
                        setState(() {
                          if (isSingle) {
                            targetSub['has_practical'] = tempHasPr;
                            targetSub['th_fm'] = tempThFm;
                            targetSub['pr_fm'] = tempPrFm;
                            targetSub['fm'] = tempHasPr ? (tempThFm + tempPrFm) : targetSub['fm'];
                            _syncSingleSubjectControllersFromMaster(_selectedSubject);
                          }
                          _recalculateAll();
                        });
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              tempHasPr
                                  ? 'Practical enabled: ${tempThFm.toInt()} Theory + ${tempPrFm.toInt()} Practical ✅'
                                  : 'Practical disabled: Single 100% Theory score ✅',
                            ),
                            backgroundColor: AppTheme.primary,
                            duration: const Duration(seconds: 2),
                          ),
                        );
                      },
                      child: const Text('Save & Apply Settings', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
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

  // --- Website Action 4: Save & Publish All Marks to Database ---
  void _saveMarks() async {
    setState(() => _saving = true);

    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);
    final classesList = teacherProvider.teacherClasses;
    final classObj = classesList.firstWhere(
      (c) => c.section == _selectedClass,
      orElse: () => classesList.isNotEmpty ? classesList.first : AppData.classes.first,
    );

    // Identify real course ID for single subject mode
    Map<String, dynamic>? selectedSubMap;
    if (!_isAllSubjectsMode) {
      selectedSubMap = _masterSubjects.firstWhere(
        (s) => s['name'] == _selectedSubject,
        orElse: () => _masterSubjects.isNotEmpty
            ? _masterSubjects.first
            : {'id': classObj.courseId ?? 1, 'name': _selectedSubject},
      );
    }

    final realSelectedCourseId = selectedSubMap != null
        ? (selectedSubMap['id'] is int
            ? selectedSubMap['id'] as int
            : int.tryParse(selectedSubMap['id']?.toString() ?? '') ?? (classObj.courseId ?? 1))
        : (classObj.courseId ?? 1);

    final List<Map<String, dynamic>> marksPayload = [];

    if (_isAllSubjectsMode) {
      // Send entries for each student and each subject with their REAL course_id!
      for (var s in _studentsMarks) {
        final sId = s['student_id'] as int;
        final controllers = s['subjectControllers'] as Map<String, TextEditingController>?;
        final thControllers = s['subjectTheoryControllers'] as Map<String, TextEditingController>?;
        final prControllers = s['subjectPracticalControllers'] as Map<String, TextEditingController>?;

        for (var sub in _masterSubjects) {
          final sName = sub['name'] as String;
          final cId = sub['id'] is int ? sub['id'] as int : int.tryParse(sub['id']?.toString() ?? '') ?? 1;
          final txt = controllers?[sName]?.text.trim() ?? '';
          if (txt.isEmpty) continue; // Skip unentered subjects so they are NOT wiped to 0!

          final score = double.tryParse(txt) ?? 0.0;
          final fm = ((sub['fm'] ?? 100) as num).toDouble();
          final hasPr = (sub['has_practical'] as bool?) ?? false;

          double? th = double.tryParse(thControllers?[sName]?.text.trim() ?? '');
          double? pr = double.tryParse(prControllers?[sName]?.text.trim() ?? '');
          if (hasPr && (th == null || pr == null)) {
            final thMax = ((sub['th_fm'] ?? 75.0) as num).toDouble();
            final prMax = ((sub['pr_fm'] ?? 25.0) as num).toDouble();
            final totFm = thMax + prMax > 0 ? (thMax + prMax) : 100.0;
            pr = score > prMax ? prMax : (score > 0 && prMax > 0 ? (score * (prMax / totFm)).clamp(0, prMax) : 0.0);
            th = (score - pr).clamp(0, thMax);
          }

          marksPayload.add({
            'student_id': sId,
            'course_id': cId,
            'course_name': sName,
            'theory': th ?? score,
            'practical': pr ?? 0.0,
            'score': score,
            'marks': score,
            'has_practical': hasPr,
            'grade': _calculateCdcGrade(score, maxMarks: fm),
          });
        }
      }
    } else {
      // Single subject payload with the selected subject's real course ID!
      final fm = _getCurrentSubjectFm();
      final hasPr = _currentSubjectHasPractical;
      for (var m in _studentsMarks) {
        double total = 0.0;
        double th = 0.0;
        double pr = 0.0;

        if (hasPr) {
          final thStr = (m['theory'] as TextEditingController).text.trim();
          final prStr = (m['practical'] as TextEditingController).text.trim();
          if (thStr.isEmpty && prStr.isEmpty) continue; // Skip unentered

          th = double.tryParse(thStr) ?? 0.0;
          pr = double.tryParse(prStr) ?? 0.0;
          total = (th + pr).clamp(0, fm);
        } else {
          final scoreStr = (m['mark'] as TextEditingController).text.trim();
          if (scoreStr.isEmpty) continue; // Skip unentered

          final score = double.tryParse(scoreStr) ?? 0.0;
          total = score.clamp(0, fm);
          th = total;
          pr = 0.0;
        }

        marksPayload.add({
          'student_id': m['student_id'],
          'course_id': realSelectedCourseId,
          'course_name': _selectedSubject,
          'theory': th,
          'practical': pr,
          'score': total,
          'marks': total,
          'has_practical': hasPr,
          'grade': _calculateCdcGrade(total, maxMarks: fm),
        });
      }
    }

    final targetClassId = classObj.classId ?? classObj.id ?? 1;
    final payload = {
      'exam_name': _selectedExam,
      'class_id': targetClassId,
      'section_id': classObj.sectionId ?? 1,
      'course_id': _isAllSubjectsMode ? 0 : realSelectedCourseId,
      'class_section': _selectedClass,
      'subject': _selectedSubject,
      'marks': marksPayload,
    };

    // Cache locally for instant offline display
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('nsh_cached_marks_${targetClassId}_$_selectedExam', jsonEncode(marksPayload));
    } catch (_) {}

    final examId = classObj.id?.toString() ?? '1';
    final res = await _apiService.submitExamMarks(examId, payload);

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
          res.success
              ? (res.message.isNotEmpty
                  ? res.message
                  : 'All grade records for $_selectedClass have been saved to the school master database.')
              : 'Marks saved locally. (Offline sync active)',
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
            },
            child: const Text('Continue Editing'),
          ),
        ],
      ),
    );
  }

  // --- Website Action 4: Individual A4 Report Card Marksheet Preview ---
  void _openReportCardModal(Map<String, dynamic> studentData) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final rollNo = studentData['roll_no'];
    final name = studentData['name'];

    // Precompute CDC Marks breakdown & Grand Totals from all entered subjects
    double grandFm = 0.0;
    double evaluatedFm = 0.0;
    double grandTh = 0.0;
    double grandPr = 0.0;
    double grandTotalObtained = 0.0;
    bool hasAnySubjectMarks = false;

    final List<Map<String, dynamic>> subjectRows = _masterSubjects.map((sub) {
      final sName = sub['name'] as String;
      final bool hasPr = (sub['has_practical'] as bool?) ?? false;
      final double fm = ((sub['fm'] ?? 100.0) as num).toDouble();
      final double thFm = ((sub['th_fm'] ?? (hasPr ? 75.0 : fm)) as num).toDouble();
      final double prFm = ((sub['pr_fm'] ?? (hasPr ? 25.0 : 0.0)) as num).toDouble();
      grandFm += fm;

      final controllers = studentData['subjectControllers'] as Map<String, TextEditingController>?;
      String scoreStr = controllers?[sName]?.text.trim() ?? '';
      if (scoreStr.isEmpty && _selectedSubject == sName) {
        scoreStr = (studentData['mark'] as TextEditingController?)?.text.trim() ?? '';
      }
      double? rawScore = double.tryParse(scoreStr);

      final thControllers = studentData['subjectTheoryControllers'] as Map<String, TextEditingController>?;
      final prControllers = studentData['subjectPracticalControllers'] as Map<String, TextEditingController>?;
      String? thText = thControllers?[sName]?.text.trim();
      String? prText = prControllers?[sName]?.text.trim();

      if ((thText == null || thText.isEmpty) && _selectedSubject == sName) {
        thText = (studentData['theory'] as TextEditingController?)?.text.trim();
      }
      if ((prText == null || prText.isEmpty) && _selectedSubject == sName) {
        prText = (studentData['practical'] as TextEditingController?)?.text.trim();
      }

      double? thVal = (thText != null && thText.isNotEmpty) ? double.tryParse(thText) : null;
      double? prVal = (prText != null && prText.isNotEmpty) ? double.tryParse(prText) : null;

      if (rawScore == null && (thVal != null || prVal != null)) {
        rawScore = (thVal ?? 0.0) + (prVal ?? 0.0);
      }

      String thDisplay = '—';
      String prDisplay = '—';
      String scoreDisplay = '—';
      String gradeDisplay = '—';

      if (rawScore != null) {
        hasAnySubjectMarks = true;
        grandTotalObtained += rawScore;
        evaluatedFm += fm;
        scoreDisplay = rawScore.toStringAsFixed(rawScore.truncateToDouble() == rawScore ? 0 : 1);
        gradeDisplay = _calculateCdcGrade(rawScore, maxMarks: fm);

        if (hasPr) {
          if (thVal == null || prVal == null) {
            final totFm = thFm + prFm > 0 ? (thFm + prFm) : 100.0;
            prVal = rawScore > prFm ? prFm : (rawScore > 0 && prFm > 0 ? (rawScore * (prFm / totFm)).clamp(0, prFm) : 0.0);
            thVal = (rawScore - prVal).clamp(0, thFm);
          }

          grandTh += thVal;
          grandPr += prVal;
          thDisplay = thVal.toStringAsFixed(thVal.truncateToDouble() == thVal ? 0 : 1);
          prDisplay = prVal.toStringAsFixed(prVal.truncateToDouble() == prVal ? 0 : 1);
        } else {
          grandTh += rawScore;
          thDisplay = rawScore.toStringAsFixed(rawScore.truncateToDouble() == rawScore ? 0 : 1);
          prDisplay = '—';
        }
      }

      return {
        'name': sName,
        'hasPr': hasPr,
        'fm': fm,
        'thFm': thFm,
        'prFm': prFm,
        'thDisplay': thDisplay,
        'prDisplay': prDisplay,
        'scoreDisplay': scoreDisplay,
        'gradeDisplay': gradeDisplay,
      };
    }).toList();

    final effectiveMaxFm = evaluatedFm > 0 ? evaluatedFm : (grandFm > 0 ? grandFm : 100.0);
    final calculatedPct = effectiveMaxFm > 0 ? (grandTotalObtained / effectiveMaxFm) * 100.0 : 0.0;
    final displayPct = hasAnySubjectMarks ? calculatedPct.toStringAsFixed(1) : '—';
    final displayGpa = hasAnySubjectMarks ? _calculateCdcGpa(calculatedPct) : '—';
    final displayGrade = hasAnySubjectMarks ? _calculateCdcGrade(calculatedPct) : '—';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) => Container(
        height: MediaQuery.of(context).size.height * 0.90,
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.25),
              blurRadius: 20,
              offset: const Offset(0, -6),
            ),
          ],
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
            const SizedBox(height: 14),

            // Modal Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.description_rounded, color: AppTheme.primary, size: 22),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Student Marksheet (अङ्क तालिका)',
                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
                      ),
                      Text(
                        'Nepal Curriculum Development Centre (CDC) Format',
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
            const Divider(height: 24),

            // Official Printable Card Layout
            Expanded(
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                child: Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkBg : const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: AppTheme.primary.withOpacity(0.25), width: 1.5),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      // School Emblems & Title
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppTheme.primary.withOpacity(0.2)),
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: Image.asset('assets/icons/app_icon.png', fit: BoxFit.cover),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'NEPAL SCHOOL HUB (NSHLMS)',
                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14, letterSpacing: 0.5, color: AppTheme.primary),
                      ),
                      const Text(
                        'Ministry of Education • NEB Affiliated Secondary School',
                        style: TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                      ),
                      const Text(
                        'काठमाडौं, नेपाल • स्थापना: २०५०',
                        style: TextStyle(fontSize: 9.5, color: AppTheme.darkSubtext),
                      ),
                      const SizedBox(height: 12),

                      // Examination Banner
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppTheme.primary,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          _selectedExam.toUpperCase(),
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 11.5, color: Colors.white, letterSpacing: 0.5),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Student Info Strip
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkCard : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppTheme.cardBorder),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Name: $name', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                                const SizedBox(height: 2),
                                Text('Roll No: $rollNo • Class: $_selectedClass', style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext)),
                              ],
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                const Text('Session: 2081 B.S.', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 11)),
                                const SizedBox(height: 2),
                                Text('Reg No: NSH-${studentData['student_id']}', style: const TextStyle(fontSize: 10, color: AppTheme.darkSubtext)),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Subject Evaluation Breakdown Table (CDC & Global Standard Layout)
                      Table(
                        border: TableBorder.all(
                          color: isDark ? AppTheme.darkCardBorder : const Color(0xFFCBD5E1),
                          width: 0.8,
                        ),
                        columnWidths: const {
                          0: FlexColumnWidth(2.6),
                          1: FlexColumnWidth(0.9),
                          2: FlexColumnWidth(1.3),
                          3: FlexColumnWidth(1.3),
                          4: FlexColumnWidth(1.1),
                          5: FlexColumnWidth(1.0),
                        },
                        defaultVerticalAlignment: TableCellVerticalAlignment.middle,
                        children: [
                          TableRow(
                            decoration: BoxDecoration(color: AppTheme.primary.withOpacity(0.12)),
                            children: const [
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 6),
                                child: Text('Subject', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 11)),
                              ),
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text('FM', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 10.5)),
                              ),
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text('Theory\n(TH)', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 10, color: Color(0xFF1E3A8A))),
                              ),
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text('Practical\n(PR)', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 10, color: Color(0xFF047857))),
                              ),
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text('Total', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 10.5)),
                              ),
                              Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text('Grade', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 10.5)),
                              ),
                            ],
                          ),
                          ...subjectRows.map((r) {
                            final bool hasPr = r['hasPr'] as bool;
                            final String gradeStr = r['gradeDisplay'] as String;
                            final String thStr = r['thDisplay'] as String;
                            final String prStr = r['prDisplay'] as String;

                            return TableRow(
                              children: [
                                Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 6),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        r['name'] as String,
                                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 11),
                                      ),
                                      if (hasPr)
                                        Text(
                                          'TH:${(r['thFm'] as double).toInt()} + PR:${(r['prFm'] as double).toInt()}',
                                          style: const TextStyle(
                                            fontSize: 8.5,
                                            fontWeight: FontWeight.w600,
                                            color: Color(0xFF047857),
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 2),
                                  child: Text(
                                    '${(r['fm'] as double).toInt()}',
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(fontSize: 10.5, color: AppTheme.darkSubtext),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 2),
                                  child: Text(
                                    thStr,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 11,
                                      color: thStr != '—' ? const Color(0xFF1E3A8A) : AppTheme.darkSubtext,
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 2),
                                  child: Text(
                                    prStr,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontWeight: hasPr ? FontWeight.w800 : FontWeight.w600,
                                      fontSize: 11,
                                      color: hasPr && prStr != '—'
                                          ? const Color(0xFF047857)
                                          : (hasPr ? AppTheme.darkSubtext : Colors.grey.shade400),
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 2),
                                  child: Text(
                                    r['scoreDisplay'] as String,
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 2),
                                  child: Text(
                                    gradeStr,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontWeight: FontWeight.w900,
                                      fontSize: 11,
                                      color: gradeStr != '—' ? _getGradeColor(gradeStr) : AppTheme.darkSubtext,
                                    ),
                                  ),
                                ),
                              ],
                            );
                          }),
                          // Grand Total Row
                          TableRow(
                            decoration: BoxDecoration(
                              color: isDark ? AppTheme.darkCard : const Color(0xFFF1F5F9),
                            ),
                            children: [
                              const Padding(
                                padding: EdgeInsets.symmetric(vertical: 8, horizontal: 6),
                                child: Text('Grand Total', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 10.5)),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text(
                                  '${grandFm.toInt()}',
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 10.5),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text(
                                  hasAnySubjectMarks ? grandTh.toStringAsFixed(grandTh.truncateToDouble() == grandTh ? 0 : 1) : '—',
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 10.5, color: Color(0xFF1E3A8A)),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text(
                                  hasAnySubjectMarks ? grandPr.toStringAsFixed(grandPr.truncateToDouble() == grandPr ? 0 : 1) : '—',
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 10.5, color: Color(0xFF047857)),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text(
                                  hasAnySubjectMarks
                                      ? grandTotalObtained.toStringAsFixed(grandTotalObtained.truncateToDouble() == grandTotalObtained ? 0 : 1)
                                      : '—',
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 11),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
                                child: Text(
                                  displayGrade,
                                  textAlign: TextAlign.center,
                                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 11, color: _getGradeColor(displayGrade)),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Final Performance Summary
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkCard : Colors.white,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppTheme.primary.withOpacity(0.3)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            Column(
                              children: [
                                const Text('Total Obtained', style: TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700)),
                                const SizedBox(height: 2),
                                Text(
                                  hasAnySubjectMarks
                                      ? '${grandTotalObtained.toStringAsFixed(grandTotalObtained.truncateToDouble() == grandTotalObtained ? 0 : 1)} / ${effectiveMaxFm.toInt()}'
                                      : '— / ${grandFm.toInt()}',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                                ),
                              ],
                            ),
                            Column(
                              children: [
                                const Text('Percentage', style: TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700)),
                                const SizedBox(height: 2),
                                Text(
                                  hasAnySubjectMarks ? '$displayPct%' : '—',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14, color: AppTheme.primary),
                                ),
                              ],
                            ),
                            Column(
                              children: [
                                const Text('GPA', style: TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700)),
                                const SizedBox(height: 2),
                                Text(
                                  displayGpa,
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14, color: AppTheme.green),
                                ),
                              ],
                            ),
                            Column(
                              children: [
                                const Text('Final Grade', style: TextStyle(fontSize: 10, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700)),
                                const SizedBox(height: 2),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: hasAnySubjectMarks ? _getGradeColor(displayGrade).withOpacity(0.15) : Colors.grey.shade100,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: hasAnySubjectMarks ? _getGradeColor(displayGrade) : Colors.grey.shade300, width: 1),
                                  ),
                                  child: Text(
                                    displayGrade,
                                    style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: hasAnySubjectMarks ? _getGradeColor(displayGrade) : AppTheme.darkSubtext),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Result Status Pill
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Text('Evaluation Status: ', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
                          Text(
                            !hasAnySubjectMarks
                                ? 'PENDING EVALUATION ⏳'
                                : (displayGrade != 'NG' && displayGrade != '—' ? 'PASSED & PROMOTED ✅' : 'NEEDS IMPROVEMENT (NG) ⚠️'),
                            style: TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 12.5,
                              color: !hasAnySubjectMarks
                                  ? AppTheme.orange
                                  : (displayGrade != 'NG' && displayGrade != '—' ? AppTheme.green : AppTheme.red),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Official Signatures
                      const Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            children: [
                              Text('................................', style: TextStyle(color: Colors.grey, fontSize: 10)),
                              Text('Class Teacher', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700)),
                            ],
                          ),
                          Column(
                            children: [
                              Text('................................', style: TextStyle(color: Colors.grey, fontSize: 10)),
                              Text('Exam Controller', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700)),
                            ],
                          ),
                          Column(
                            children: [
                              Text('................................', style: TextStyle(color: Colors.grey, fontSize: 10)),
                              Text('Principal Seal', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700)),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    // Instant initial route transition: show full loading symbol immediately on Frame 1
    if (_isInitializing) {
      return Scaffold(
        backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
        appBar: AppBar(
          title: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Enter Marks & Grades',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
              ),
              Text(
                'Direct Master Grade Ledger • CDC Nepal NEB Standard',
                style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppTheme.darkSubtext),
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
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 72,
                height: 72,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withOpacity(0.12),
                  shape: BoxShape.circle,
                ),
                child: const CircularProgressIndicator(
                  strokeWidth: 3.2,
                  valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primary),
                ),
              ),
              const SizedBox(height: 18),
              const Text(
                'Loading Marks Ledger...',
                style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
              ),
              const SizedBox(height: 8),
              const Text(
                'Fetching student evaluations and CDC curriculum.\nPlease wait a moment...',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: AppTheme.darkSubtext),
              ),
            ],
          ),
        ),
      );
    }

    final isMobile = MediaQuery.of(context).size.width < 720;
    final showSpreadsheet = _isSpreadsheetView ?? !isMobile;

    final teacherProvider = Provider.of<TeacherProvider>(context);
    final classesList = teacherProvider.teacherClasses;

    final classSections = classesList.map((c) => c.section).toSet().toList();
    if (classSections.isEmpty) classSections.add('Grade 10 - A');

    if (_selectedClass == null || !classSections.contains(_selectedClass)) {
      _selectedClass = classSections.first;
    }

    final classObj = classesList.firstWhere(
      (c) => c.section == _selectedClass,
      orElse: () => classesList.isNotEmpty ? classesList.first : AppData.classes.first,
    );

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final allStudents = teacherProvider.studentList;
    final classStudents = allStudents.where((s) {
      if (classObj.classId != null && s.classId != null && s.classId != classObj.classId) return false;
      if (classObj.sectionId != null && s.sectionId != null && s.sectionId != classObj.sectionId) return false;
      return true;
    }).toList();

    final activeStudents = classStudents.isNotEmpty ? classStudents : allStudents;

    // Determine if logged-in teacher is Class In-Charge (कक्षा शिक्षक) of the selected class
    final matchingClassEntries = classesList.where((c) => c.section == _selectedClass).toList();
    final bool isClassIncharge = matchingClassEntries.any((c) => c.isClassTeacher) ||
        authProvider.isAdminRole ||
        (teacherProvider.classes.any((cls) {
          if (cls is Map) {
            final sec = cls['section']?.toString() ??
                (cls['class_name'] != null ? '${cls['class_name']} — ${cls['section_name'] ?? 'A'}' : '');
            return (sec == _selectedClass || cls['section'] == _selectedClass) &&
                (cls['is_class_teacher'] == true || cls['is_class_teacher'] == 1 || cls['is_class_teacher'] == '1');
          }
          return false;
        }));

    // If regular teacher, enforce single subject mode directly without queuing another frame
    if (!isClassIncharge && _isAllSubjectsMode) {
      _isAllSubjectsMode = false;
      final availableNames = _masterSubjects.map((s) => s['name'] as String).toList();
      _selectedSubject = availableNames.isNotEmpty ? availableNames.first : 'General';
      _syncSingleSubjectControllersFromMaster(_selectedSubject);
    }

    // Populate students when class or student count changes (never wipe when changing subjects!)
    final targetClassId = classObj.classId ?? classObj.id ?? 1;
    final currentKey = '${targetClassId}_${classObj.sectionId ?? 1}_${activeStudents.length}';
    if (_lastClassKey != currentKey || _studentsMarks.isEmpty) {
      _lastClassKey = currentKey;
      _populateStudents(activeStudents);
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _loadExistingMarks(targetClassId, classObj.sectionId, classObj.courseId);
        if (_loadedClassIdForSubjects != targetClassId && !_isLoadingSubjects) {
          _fetchClassSubjects(targetClassId, classesList, _selectedClass!);
        }
      });
    } else if (_loadedClassIdForSubjects != targetClassId && !_isLoadingSubjects) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _fetchClassSubjects(targetClassId, classesList, _selectedClass!);
      });
    }

    // Filter students by search query
    final filteredMarks = _studentsMarks.where((item) {
      if (_searchQuery.isEmpty) return true;
      final q = _searchQuery.toLowerCase();
      final name = (item['name'] as String).toLowerCase();
      final roll = item['roll_no'].toString().toLowerCase();
      return name.contains(q) || roll.contains(q);
    }).toList();

    // Average Class Score
    final avgScore = _studentsMarks.isEmpty
        ? '0.0'
        : (_studentsMarks.map((e) => (e['percentage'] as num)).reduce((a, b) => a + b) / _studentsMarks.length)
            .toStringAsFixed(1);

    return PopScope(
      canPop: _currentStep == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop && _currentStep == 1) {
          setState(() => _currentStep = 0);
        }
      },
      child: _currentStep == 0
          ? _buildSetupScreen(
              context: context,
              isDark: isDark,
              classSections: classSections,
              classesList: classesList,
              classObj: classObj,
              activeStudents: activeStudents,
              isClassIncharge: isClassIncharge,
            )
          : _buildMarksScreen(
              context: context,
              isDark: isDark,
              filteredMarks: filteredMarks,
              activeStudents: activeStudents,
              avgScore: avgScore,
              showSpreadsheet: showSpreadsheet,
              isClassIncharge: isClassIncharge,
            ),
    );
  }

  // =========================================================================
  // SCREEN 1: Setup & Evaluation Parameters ("Set Everything First")
  // =========================================================================
  Widget _buildSetupScreen({
    required BuildContext context,
    required bool isDark,
    required List<String> classSections,
    required List<TeacherClass> classesList,
    required TeacherClass classObj,
    required List<dynamic> activeStudents,
    required bool isClassIncharge,
  }) {
    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Marks Entry Setup',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
            ),
            Text(
              'Step 1 of 2 • Select Exam, Class & Evaluation Scope',
              style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppTheme.darkSubtext),
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
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.only(bottom: 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Top Loading Progress Indicator
            if (_isLoading || _saving)
              const SizedBox(
                height: 3,
                child: LinearProgressIndicator(
                  backgroundColor: Colors.transparent,
                  valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primary),
                ),
              ),

            // Step Indicator Strip
            Container(
              margin: const EdgeInsets.fromLTRB(16, 12, 16, 6),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.primary,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.tune_rounded, size: 12, color: Colors.white),
                        SizedBox(width: 5),
                        Text(
                          '1. Configuration',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Icon(Icons.arrow_forward_rounded, size: 14, color: AppTheme.darkSubtext),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: isDark ? AppTheme.darkBg : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.table_chart_rounded, size: 12, color: AppTheme.darkSubtext),
                        SizedBox(width: 5),
                        Text(
                          '2. Enter Marks',
                          style: TextStyle(color: AppTheme.darkSubtext, fontWeight: FontWeight.w700, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // 1. Website Status Bar: Submission Window Status
            Container(
              margin: const EdgeInsets.fromLTRB(16, 6, 16, 4),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: _isWindowOpen
                    ? (isDark ? const Color(0xFF064E3B).withOpacity(0.4) : const Color(0xFFECFDF5))
                    : (isDark ? const Color(0xFF78350F).withOpacity(0.4) : const Color(0xFFFFFBEB)),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _isWindowOpen
                      ? const Color(0xFF10B981).withOpacity(0.4)
                      : const Color(0xFFF59E0B).withOpacity(0.4),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    _isWindowOpen ? Icons.lock_open_rounded : Icons.lock_rounded,
                    size: 16,
                    color: _isWindowOpen ? const Color(0xFF059669) : const Color(0xFFD97706),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _isWindowOpen ? 'Submission Window is OPEN' : 'Submission Window Closed',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: _isWindowOpen ? const Color(0xFF065F46) : const Color(0xFF92400E),
                    ),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                    decoration: BoxDecoration(
                      color: _isWindowOpen ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      _isWindowOpen ? 'ACTIVE' : 'LOCKED',
                      style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w900, color: Colors.white),
                    ),
                  ),
                ],
              ),
            ),

            // 2. Class In-Charge vs Subject Teacher Role Header
            Container(
              margin: const EdgeInsets.fromLTRB(16, 2, 16, 6),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isClassIncharge
                    ? (isDark ? const Color(0xFF78350F).withOpacity(0.3) : const Color(0xFFFEF3C7))
                    : (isDark ? const Color(0xFF1E293B).withOpacity(0.6) : const Color(0xFFF1F5F9)),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isClassIncharge
                      ? const Color(0xFFF59E0B).withOpacity(0.5)
                      : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(5),
                    decoration: BoxDecoration(
                      color: isClassIncharge ? const Color(0xFFF59E0B) : AppTheme.primary,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      isClassIncharge ? Icons.stars_rounded : Icons.person_rounded,
                      size: 15,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              isClassIncharge
                                  ? 'Class In-Charge (कक्षा शिक्षक)'
                                  : 'Subject Teacher (विषय शिक्षक)',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w900,
                                color: isClassIncharge
                                    ? (isDark ? const Color(0xFFFCD34D) : const Color(0xFFB45309))
                                    : (isDark ? Colors.white : const Color(0xFF334155)),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                              decoration: BoxDecoration(
                                color: isClassIncharge
                                    ? const Color(0xFFF59E0B).withOpacity(0.2)
                                    : AppTheme.primary.withOpacity(0.15),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                isClassIncharge ? 'ALL SUBJECTS ACCESS' : 'SINGLE SUBJECT ONLY',
                                style: TextStyle(
                                  fontSize: 8.5,
                                  fontWeight: FontWeight.w900,
                                  color: isClassIncharge ? const Color(0xFFD97706) : AppTheme.primary,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          isClassIncharge
                              ? 'You have full Master Ledger access to enter all subjects for $_selectedClass.'
                              : 'Access restricted to your assigned subject ($_selectedSubject). Only Class In-Charge can enter all subjects.',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: isDark ? AppTheme.darkSubtext : const Color(0xFF64748B),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // 3. Main Configuration Card: Exam, Class, Section, Subject / Scope
            Container(
              margin: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.tune_rounded, size: 16, color: AppTheme.primary),
                      SizedBox(width: 8),
                      Text(
                        'Evaluation Parameters',
                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Exam Selector
                  _buildSelector(
                    'Exam / Terminal Assessment',
                    _selectedExam,
                    [
                      'First Terminal Examination 2081',
                      'Second Terminal Examination 2081',
                      'Third Terminal Examination 2081',
                      'Annual Examination 2081',
                    ],
                    (v) {
                      if (v != null && v != _selectedExam) {
                        setState(() => _selectedExam = v);
                        final targetObj = classesList.firstWhere(
                          (c) => c.section == _selectedClass,
                          orElse: () => classesList.first,
                        );
                        final targetId = targetObj.classId ?? targetObj.id ?? 1;
                        _loadExistingMarks(targetId, targetObj.sectionId, targetObj.courseId);
                      }
                    },
                    isDark,
                  ),
                  const SizedBox(height: 12),

                  // Class Selector
                  _buildSelector(
                    'Class / Grade',
                    _selectedClass!,
                    classSections,
                    (v) {
                      if (v != null && v != _selectedClass) {
                        setState(() {
                          _selectedClass = v;
                          _selectedSubject = 'all';
                          _isAllSubjectsMode = true;
                          _lastClassKey = null; // force reload for new class
                          _loadedClassIdForSubjects = null;
                        });
                        final targetObj = classesList.firstWhere(
                          (c) => c.section == v,
                          orElse: () => classesList.first,
                        );
                        final targetId = targetObj.classId ?? targetObj.id ?? 1;
                        _fetchClassSubjects(targetId, classesList, v);
                      }
                    },
                    isDark,
                  ),
                  const SizedBox(height: 12),

                  // Subject Selector
                  _buildSelector(
                    'Subject / Scope',
                    _selectedSubject,
                    isClassIncharge
                        ? [
                            'all',
                            ..._masterSubjects.map((s) => s['name'] as String).toSet(),
                          ]
                        : [
                            ..._masterSubjects.map((s) => s['name'] as String).toSet(),
                          ],
                    (v) {
                      if (v == null) return;
                      if (v == 'all' && !isClassIncharge) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Access Restricted: Only Class In-Charge can enter all subjects! ⚠️'),
                            backgroundColor: AppTheme.red,
                          ),
                        );
                        return;
                      }
                      setState(() {
                        _selectedSubject = v;
                        _isAllSubjectsMode = (v == 'all');
                        if (!_isAllSubjectsMode) {
                          _syncSingleSubjectControllersFromMaster(v);
                        }
                        _recalculateAll();
                      });
                    },
                    isDark,
                    displayMap: {
                      'all': '⚡ All Subjects (Master Ledger)',
                    },
                  ),
                ],
              ),
            ),

            // 4. Practical Settings Shortcut Card
            Container(
              margin: const EdgeInsets.fromLTRB(16, 2, 16, 8),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withOpacity(0.12),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.science_rounded, size: 16, color: AppTheme.primary),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Practical / CAS Evaluation',
                          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12),
                        ),
                        Text(
                          _isAllSubjectsMode
                              ? '${_masterSubjects.where((s) => s['has_practical'] == true).length} of ${_masterSubjects.length} subjects have practical components'
                              : (_currentSubjectHasPractical
                                  ? 'Practical FM: ${_getCurrentSubjectPrFm().toInt()} | Theory FM: ${_getCurrentSubjectThFm().toInt()}'
                                  : 'Theory Only (No Practical component)'),
                          style: const TextStyle(fontSize: 10.5, color: AppTheme.darkSubtext),
                        ),
                      ],
                    ),
                  ),
                  TextButton.icon(
                    onPressed: () => _openPracticalSettingsModal(),
                    icon: const Icon(Icons.settings_rounded, size: 14),
                    label: const Text('Configure', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
                    style: TextButton.styleFrom(
                      foregroundColor: AppTheme.primary,
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    ),
                  ),
                ],
              ),
            ),

            // 5. Evaluation Summary Card
            Container(
              margin: const EdgeInsets.fromLTRB(16, 2, 16, 12),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.check_circle_rounded, size: 15, color: AppTheme.green),
                      SizedBox(width: 6),
                      Text(
                        'Ready for Evaluation',
                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12.5),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  _buildSummaryRow(Icons.school_rounded, 'Class', '$_selectedClass (${activeStudents.length} Students)', isDark),
                  const SizedBox(height: 6),
                  _buildSummaryRow(Icons.assignment_rounded, 'Exam', _selectedExam, isDark),
                  const SizedBox(height: 6),
                  _buildSummaryRow(
                    Icons.book_rounded,
                    'Scope',
                    _isAllSubjectsMode
                        ? 'All ${_masterSubjects.length} Curriculum Subjects (Master Ledger)'
                        : '$_selectedSubject (${_currentSubjectHasPractical ? "Theory + Practical" : "Theory Only"})',
                    isDark,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.fromLTRB(16, 10, 16, 14),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.darkCard : Colors.white,
          border: Border(top: BorderSide(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.25 : 0.05),
              blurRadius: 10,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        child: SafeArea(
          child: SizedBox(
            height: 48,
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                setState(() => _currentStep = 1);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 0,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.table_chart_rounded, size: 18),
                  const SizedBox(width: 8),
                  Text(
                    'Enter Marks (${activeStudents.length} Students) →',
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // =========================================================================
  // SCREEN 2: Marks Ledger Workspace ("Enter Marks Properly")
  // =========================================================================
  Widget _buildMarksScreen({
    required BuildContext context,
    required bool isDark,
    required List<Map<String, dynamic>> filteredMarks,
    required List<dynamic> activeStudents,
    required String avgScore,
    required bool showSpreadsheet,
    required bool isClassIncharge,
  }) {
    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: InkWell(
          onTap: () => setState(() => _currentStep = 0),
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 2),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Flexible(
                      child: Text(
                        '$_selectedClass • ${_isAllSubjectsMode ? "Master Ledger" : _selectedSubject}',
                        style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 4),
                    const Icon(Icons.tune_rounded, size: 13, color: AppTheme.primary),
                  ],
                ),
                Text(
                  '$_selectedExam • ${activeStudents.length} Students',
                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppTheme.darkSubtext),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new_rounded,
          onTap: () => setState(() => _currentStep = 0),
          size: 38,
        ),
        leadingWidth: 60,
        automaticallyImplyLeading: false,
        actions: [
          // Change Setup / Filter Button
          IconButton(
            icon: const Icon(Icons.tune_rounded, size: 20, color: AppTheme.primary),
            tooltip: 'Change Setup & Filters',
            onPressed: () => setState(() => _currentStep = 0),
          ),
          // Table vs Cards Toggle
          IconButton(
            icon: Icon(
              showSpreadsheet ? Icons.view_agenda_rounded : Icons.table_chart_rounded,
              color: AppTheme.primary,
              size: 20,
            ),
            tooltip: showSpreadsheet ? 'Switch to Card List' : 'Switch to Master Table',
            onPressed: () => setState(() => _isSpreadsheetView = !showSpreadsheet),
          ),
          // Save All Button
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
                _saving ? 'Saving...' : 'Save All',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900),
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
          // Top Loading Progress Indicator
          if (_isLoading || _saving)
            const SizedBox(
              height: 3,
              child: LinearProgressIndicator(
                backgroundColor: Colors.transparent,
                valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primary),
              ),
            ),

          // 1. Action Toolbar (Above Filter: Set 0, Recalculate, Practical, Report Cards)
          Container(
            height: 38,
            margin: const EdgeInsets.fromLTRB(0, 8, 0, 4),
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              children: [
                // Set Empty to 0
                _buildToolbarButton(
                  icon: Icons.auto_awesome_rounded,
                  label: 'Set Empty to 0',
                  color: const Color(0xFF1E40AF),
                  backgroundColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFEEF2F6),
                  borderColor: isDark ? const Color(0xFF3B82F6) : const Color(0xFFBFDBFE),
                  textColor: isDark ? const Color(0xFF93C5FD) : const Color(0xFF1E40AF),
                  onTap: _fillZeroForEmpty,
                  isDark: isDark,
                ),
                const SizedBox(width: 8),

                // Recalculate
                _buildToolbarButton(
                  icon: Icons.refresh_rounded,
                  label: 'Recalculate',
                  color: const Color(0xFF059669),
                  backgroundColor: isDark ? const Color(0xFF064E3B).withOpacity(0.3) : const Color(0xFFECFDF5),
                  borderColor: isDark ? const Color(0xFF10B981) : const Color(0xFFA7F3D0),
                  textColor: isDark ? const Color(0xFF6EE7B7) : const Color(0xFF059669),
                  onTap: _recalculateAll,
                  isDark: isDark,
                ),
                const SizedBox(width: 8),

                // Set Practical Evaluation Button
                _buildToolbarButton(
                  icon: _currentSubjectHasPractical ? Icons.science_rounded : Icons.science_outlined,
                  label: _isAllSubjectsMode
                      ? 'Practical (${_masterSubjects.where((s) => s['has_practical'] == true).length}/${_masterSubjects.length})'
                      : (_currentSubjectHasPractical
                          ? 'Practical: YES (${_getCurrentSubjectThFm().toInt()}+${_getCurrentSubjectPrFm().toInt()})'
                          : 'Practical: NO'),
                  color: const Color(0xFF64748B),
                  backgroundColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
                  borderColor: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1),
                  textColor: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                  onTap: () => _openPracticalSettingsModal(),
                  isDark: isDark,
                ),
                const SizedBox(width: 8),

                // Autofill Practical (CAS)
                if (_currentSubjectHasPractical) ...[
                  _buildToolbarButton(
                    icon: Icons.bolt_rounded,
                    label: _isAllSubjectsMode ? 'Autofill PR' : 'Autofill PR (${_getCurrentSubjectPrFm().toInt()})',
                    color: AppTheme.orange,
                    onTap: _autoFillFullPracticals,
                    isDark: isDark,
                  ),
                  const SizedBox(width: 8),
                ],

                // Print Class Marksheets (Individual A4)
                _buildToolbarButton(
                  icon: Icons.print_rounded,
                  label: 'Print Marksheets (A4)',
                  color: const Color(0xFF7C3AED),
                  backgroundColor: isDark ? const Color(0xFF4C1D95).withOpacity(0.3) : const Color(0xFFF5F3FF),
                  borderColor: isDark ? const Color(0xFF8B5CF6) : const Color(0xFFDDD6FE),
                  textColor: isDark ? const Color(0xFFC4B5FD) : const Color(0xFF7C3AED),
                  onTap: () {
                    if (_studentsMarks.isNotEmpty) {
                      _openReportCardModal(_studentsMarks.first);
                    }
                  },
                  isDark: isDark,
                ),
              ],
            ),
          ),

          // 2. Quick Search Filter & Class Average Stats Strip
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 2, 16, 6),
            child: Row(
              children: [
                // Search Input
                Expanded(
                  child: SizedBox(
                    height: 38,
                    child: TextField(
                      controller: _searchCtrl,
                      onChanged: (v) => setState(() => _searchQuery = v),
                      decoration: InputDecoration(
                        hintText: 'Filter by student name or roll...',
                        hintStyle: const TextStyle(fontSize: 11.5, color: AppTheme.darkSubtext),
                        prefixIcon: const Icon(Icons.search_rounded, size: 16, color: AppTheme.darkSubtext),
                        suffixIcon: _searchQuery.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear, size: 14),
                                onPressed: () {
                                  _searchCtrl.clear();
                                  setState(() => _searchQuery = '');
                                },
                              )
                            : null,
                        contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 10),
                        filled: true,
                        fillColor: isDark ? AppTheme.darkCard : Colors.white,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: BorderSide(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: BorderSide(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),

                // Class Avg Pill
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkCard : Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.analytics_rounded, size: 14, color: AppTheme.primary),
                      const SizedBox(width: 4),
                      Text(
                        'Avg: $avgScore%',
                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w900, color: AppTheme.primary),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Live Sync Indicator Banner
          if (_isLoading)
            Container(
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 6),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: AppTheme.primary.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppTheme.primary.withOpacity(0.25)),
              ),
              child: Row(
                children: [
                  const SizedBox(
                    width: 13,
                    height: 13,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primary),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _isLoadingSubjects
                          ? 'Loading class curriculum from server... Please wait.'
                          : 'Fetching latest student marks from server... Please wait.',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.primary,
                      ),
                    ),
                  ),
                ],
              ),
            ),

          // 3. Main Evaluation Body (Ledger Table vs Mobile Cards)
          Expanded(
            child: showSpreadsheet
                ? _buildSpreadsheetTable(filteredMarks, isDark)
                : _buildCardListView(filteredMarks, isDark),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(IconData icon, String label, String value, bool isDark) {
    return Row(
      children: [
        Icon(icon, size: 14, color: AppTheme.primary),
        const SizedBox(width: 8),
        Text(
          '$label: ',
          style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  // --- Spreadsheet Master Grade Ledger (Horizontal Scrollable Table) ---
  Widget _buildSpreadsheetTable(List<Map<String, dynamic>> marksList, bool isDark) {
    if (marksList.isEmpty) {
      if (_isLoading) {
        return Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const SizedBox(
                  width: 38,
                  height: 38,
                  child: CircularProgressIndicator(strokeWidth: 3, color: AppTheme.primary),
                ),
                const SizedBox(height: 18),
                const Text(
                  'Loading Marks Ledger...',
                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Fetching students and academic records from server.\nPlease wait a moment...',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 11.5, color: AppTheme.darkSubtext),
                ),
              ],
            ),
          ),
        );
      }
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.search_off_rounded, size: 48, color: AppTheme.grey),
            const SizedBox(height: 8),
            Text(
              _searchQuery.isNotEmpty ? 'No students match "$_searchQuery"' : 'No students found for this class',
              style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.darkSubtext),
            ),
          ],
        ),
      );
    }

    const double kHeaderHeight = 52.0;
    const double kRowHeight = 54.0;
    const double kLeftPaneWidth = 50.0; // ONLY Roll column pinned on the left!

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 2, 16, 16),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          // 1. TOP STICKY HEADER ROW (NEVER SLIDES UP! Fixed at top)
          _buildStickyHeaderRow(isDark, kHeaderHeight, kLeftPaneWidth),

          // 2. SCROLLABLE DATA ROWS (Scrolls vertically through students)
          Expanded(
            child: Scrollbar(
              thumbVisibility: true,
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                scrollDirection: Axis.vertical,
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 2a. FROZEN LEFT COLUMN: ONLY Roll No. (Fixed horizontally)
                    _buildFrozenLeftDataRows(marksList, isDark, kRowHeight, kLeftPaneWidth),

                    // 2b. HORIZONTALLY SCROLLABLE DATA CELLS: Student Name + Inputs + Results
                    Expanded(
                      child: _buildScrollableRightDataRows(marksList, isDark, kRowHeight),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- Sticky Top Header Row (Pinned at top, never slides up!) ---
  Widget _buildStickyHeaderRow(bool isDark, double headerHeight, double leftWidth) {
    return Container(
      height: headerHeight,
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : const Color(0xFFF8FAFC),
        border: Border(
          bottom: BorderSide(
            color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
            width: 1.0,
          ),
        ),
      ),
      child: Row(
        children: [
          // Top-Left Frozen Corner: ONLY Roll Header (Width 50px)
          Container(
            width: leftWidth,
            height: headerHeight,
            decoration: BoxDecoration(
              border: Border(
                right: BorderSide(
                  color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                  width: 1.0,
                ),
              ),
            ),
            child: Center(
              child: Text(
                'Roll',
                style: TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                  color: isDark ? Colors.white70 : const Color(0xFF334155),
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),

          // Top-Right Horizontally Scrollable Headers: Student Name + Subjects
          Expanded(
            child: NotificationListener<ScrollNotification>(
              onNotification: (notification) {
                if (notification is ScrollUpdateNotification) {
                  _syncHorizontalScroll(_headerHorizontalCtrl, _bodyHorizontalCtrl);
                }
                return false;
              },
              child: SingleChildScrollView(
                controller: _headerHorizontalCtrl,
                scrollDirection: Axis.horizontal,
                physics: const ClampingScrollPhysics(),
                child: _buildRightHeaderContent(isDark, headerHeight),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRightHeaderContent(bool isDark, double headerHeight) {
    const double kColStudent = 140.0; // Student column slides away when scrolling horizontally!
    const double kColSubject = 92.0;
    const double kColTotal = 66.0;
    const double kColPct = 60.0;
    const double kColGpa = 58.0;
    const double kColGrade = 66.0;
    const double kColMarksheet = 90.0;

    return Row(
      children: [
        // 1. Student Name Header (Slides left and hides when scrolling horizontally!)
        SizedBox(
          width: kColStudent,
          height: headerHeight,
          child: Align(
            alignment: Alignment.centerLeft,
            child: Padding(
              padding: const EdgeInsets.only(left: 14),
              child: Text(
                'Student',
                style: TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                  color: isDark ? Colors.white70 : const Color(0xFF334155),
                ),
              ),
            ),
          ),
        ),

        // 2. Subject Columns
        if (_isAllSubjectsMode) ...[
          ..._masterSubjects.map((sub) {
            final bool hasPr = (sub['has_practical'] as bool?) ?? false;
            return SizedBox(
              width: kColSubject,
              height: headerHeight,
              child: InkWell(
                onTap: () => _openPracticalSettingsModal(subjectOverride: sub),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Flexible(
                            child: Text(
                              sub['name'] as String,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontWeight: FontWeight.w700,
                                fontSize: 12,
                                color: isDark ? Colors.white : const Color(0xFF1E293B),
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ),
                          if (hasPr) ...[
                            const SizedBox(width: 3),
                            const Icon(Icons.science_rounded, size: 10, color: AppTheme.primary),
                          ],
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        hasPr
                            ? 'TH:${(sub['th_fm'] ?? 75).toInt()} | PR:${(sub['pr_fm'] ?? 25).toInt()}'
                            : 'FM:${sub['fm'].toInt()} | PM:${sub['pm'].toInt()}',
                        style: TextStyle(
                          fontSize: 9.5,
                          color: hasPr ? AppTheme.primary : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                          fontWeight: FontWeight.w600,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ] else ...[
          if (_currentSubjectHasPractical) ...[
            SizedBox(
              width: kColSubject,
              height: headerHeight,
              child: Center(
                child: Text(
                  'Theory (${_getCurrentSubjectThFm().toInt()})',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                    color: isDark ? Colors.white : const Color(0xFF1E293B),
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ),
            SizedBox(
              width: kColSubject,
              height: headerHeight,
              child: Center(
                child: Text(
                  'Practical (${_getCurrentSubjectPrFm().toInt()})',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                    color: isDark ? Colors.white : const Color(0xFF1E293B),
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ),
          ] else ...[
            SizedBox(
              width: kColSubject,
              height: headerHeight,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Marks (${_getCurrentSubjectFm().toInt()})',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 12,
                      color: isDark ? Colors.white : const Color(0xFF1E293B),
                    ),
                    textAlign: TextAlign.center,
                  ),
                  Text(
                    'PM:${_getCurrentSubjectPm().toInt()}',
                    style: TextStyle(
                      fontSize: 9.5,
                      color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                      fontWeight: FontWeight.w600,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ],
        ],

        // 3. Calculated Columns Headers
        SizedBox(
          width: kColTotal,
          child: Center(
            child: Text(
              'Total',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5, color: isDark ? Colors.white70 : const Color(0xFF334155)),
            ),
          ),
        ),
        SizedBox(
          width: kColPct,
          child: Center(
            child: Text(
              '%',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5, color: isDark ? Colors.white70 : const Color(0xFF334155)),
            ),
          ),
        ),
        SizedBox(
          width: kColGpa,
          child: Center(
            child: Text(
              'GPA',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5, color: isDark ? Colors.white70 : const Color(0xFF334155)),
            ),
          ),
        ),
        SizedBox(
          width: kColGrade,
          child: Center(
            child: Text(
              'Grade',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5, color: isDark ? Colors.white70 : const Color(0xFF334155)),
            ),
          ),
        ),
        SizedBox(
          width: kColMarksheet,
          child: Center(
            child: Text(
              'Marksheet',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: isDark ? Colors.white70 : const Color(0xFF334155)),
            ),
          ),
        ),
      ],
    );
  }

  // --- Frozen Left Data Rows (ONLY Roll No. Pinned Horizontally) ---
  Widget _buildFrozenLeftDataRows(
    List<Map<String, dynamic>> marksList,
    bool isDark,
    double rowHeight,
    double leftWidth,
  ) {
    return Container(
      width: leftWidth,
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        border: Border(
          right: BorderSide(
            color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
            width: 1.0,
          ),
        ),
      ),
      child: Column(
        children: marksList.asMap().entries.map((entry) {
          final idx = entry.key;
          final s = entry.value;
          final isEven = idx.isEven;

          return Container(
            width: leftWidth,
            height: rowHeight,
            decoration: BoxDecoration(
              color: isEven
                  ? (isDark ? Colors.white.withOpacity(0.02) : const Color(0xFFFAFCFF))
                  : Colors.transparent,
              border: Border(
                bottom: BorderSide(
                  color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
                  width: 1.0,
                ),
              ),
            ),
            child: Center(
              child: Text(
                '${s['roll_no']}',
                style: const TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 14,
                  color: Color(0xFF0284C7),
                ),
                textAlign: TextAlign.center,
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  // --- Scrollable Right Data Rows (Student Name + Inputs & Results) ---
  Widget _buildScrollableRightDataRows(
    List<Map<String, dynamic>> marksList,
    bool isDark,
    double rowHeight,
  ) {
    return Scrollbar(
      controller: _bodyHorizontalCtrl,
      thumbVisibility: true,
      child: NotificationListener<ScrollNotification>(
        onNotification: (notification) {
          if (notification is ScrollUpdateNotification) {
            _syncHorizontalScroll(_bodyHorizontalCtrl, _headerHorizontalCtrl);
          }
          return false;
        },
        child: SingleChildScrollView(
          controller: _bodyHorizontalCtrl,
          scrollDirection: Axis.horizontal,
          physics: const ClampingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: marksList.asMap().entries.map((entry) {
              final idx = entry.key;
              final s = entry.value;
              return _buildRightDataRowItem(idx, s, isDark, rowHeight);
            }).toList(),
          ),
        ),
      ),
    );
  }

  Widget _buildRightDataRowItem(
    int idx,
    Map<String, dynamic> s,
    bool isDark,
    double rowHeight,
  ) {
    const double kColStudent = 140.0; // Student column slides away when scrolling horizontally!
    const double kColSubject = 92.0;
    const double kColTotal = 66.0;
    const double kColPct = 60.0;
    const double kColGpa = 58.0;
    const double kColGrade = 66.0;
    const double kColMarksheet = 90.0;

    final isEven = idx.isEven;
    final studentName = (s['name'] as String?) ?? 'Student';
    final initial = studentName.isNotEmpty ? studentName.substring(0, 1).toUpperCase() : '?';
    final grade = s['grade'] as String;
    final gpa = s['gpa'] as String;
    final pct = (s['percentage'] as num).toStringAsFixed(1);
    final total = (s['total'] as num).toStringAsFixed(0);
    final gradeColor = _getGradeColor(grade);

    return Container(
      height: rowHeight,
      decoration: BoxDecoration(
        color: isEven
            ? (isDark ? Colors.white.withOpacity(0.02) : const Color(0xFFFAFCFF))
            : Colors.transparent,
        border: Border(
          bottom: BorderSide(
            color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
            width: 1.0,
          ),
        ),
      ),
      child: Row(
        children: [
          // 1. Student Avatar + Name Cell (Slides left and hides when scrolling horizontally!)
          SizedBox(
            width: kColStudent,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 10),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 13,
                    backgroundColor: isDark
                        ? const Color(0xFF1E3A8A).withOpacity(0.4)
                        : const Color(0xFFE0F2FE),
                    child: Text(
                      initial,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF0369A1),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      studentName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 12.5,
                        color: isDark ? Colors.white : const Color(0xFF1E293B),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 2. Subject Inputs (Stadium pill design from web + Fast 0-delay instant keypad)
          if (_isAllSubjectsMode) ...[
            ..._masterSubjects.map((sub) {
              final sName = sub['name'] as String;
              final controllers = s['subjectControllers'] as Map<String, TextEditingController>?;
              final ctrl = controllers?[sName];

              return SizedBox(
                width: kColSubject,
                child: Center(
                  child: SizedBox(
                    width: 70,
                    height: 33,
                    child: TextField(
                      controller: ctrl,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                      textInputAction: TextInputAction.next,
                      enableSuggestions: false,
                      autocorrect: false,
                      enableInteractiveSelection: false,
                      scrollPadding: EdgeInsets.zero,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      decoration: InputDecoration(
                        isDense: true,
                        hintText: '—',
                        hintStyle: TextStyle(
                          color: isDark ? Colors.white24 : AppTheme.darkSubtext.withOpacity(0.35),
                          fontSize: 11,
                        ),
                        contentPadding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        filled: true,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                        ),
                      ),
                      onChanged: (_) => _recalculateRow(idx),
                    ),
                  ),
                ),
              );
            }),
          ] else ...[
            if (_currentSubjectHasPractical) ...[
              // Theory Cell
              SizedBox(
                width: kColSubject,
                child: Center(
                  child: SizedBox(
                    width: 70,
                    height: 33,
                    child: TextField(
                      controller: s['theory'] as TextEditingController?,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                      textInputAction: TextInputAction.next,
                      enableSuggestions: false,
                      autocorrect: false,
                      enableInteractiveSelection: false,
                      scrollPadding: EdgeInsets.zero,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      decoration: InputDecoration(
                        isDense: true,
                        hintText: '—',
                        contentPadding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        filled: true,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                        ),
                      ),
                      onChanged: (_) => _recalculateRow(idx),
                    ),
                  ),
                ),
              ),
              // Practical Cell
              SizedBox(
                width: kColSubject,
                child: Center(
                  child: SizedBox(
                    width: 70,
                    height: 33,
                    child: TextField(
                      controller: s['practical'] as TextEditingController?,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                      textInputAction: TextInputAction.next,
                      enableSuggestions: false,
                      autocorrect: false,
                      enableInteractiveSelection: false,
                      scrollPadding: EdgeInsets.zero,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      decoration: InputDecoration(
                        isDense: true,
                        hintText: '—',
                        contentPadding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        filled: true,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                        ),
                      ),
                      onChanged: (_) => _recalculateRow(idx),
                    ),
                  ),
                ),
              ),
            ] else ...[
              // Single Subject Marks
              SizedBox(
                width: kColSubject,
                child: Center(
                  child: SizedBox(
                    width: 70,
                    height: 33,
                    child: TextField(
                      controller: s['mark'] as TextEditingController?,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                      textInputAction: TextInputAction.next,
                      enableSuggestions: false,
                      autocorrect: false,
                      enableInteractiveSelection: false,
                      scrollPadding: EdgeInsets.zero,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      decoration: InputDecoration(
                        isDense: true,
                        hintText: '—',
                        contentPadding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        filled: true,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                            width: 1.2,
                          ),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                        ),
                      ),
                      onChanged: (_) {
                        _syncMasterFromSingleSubject(idx, _selectedSubject);
                        _recalculateRow(idx);
                      },
                    ),
                  ),
                ),
              ),
            ],
          ],

          // 3. Total
          SizedBox(
            width: kColTotal,
            child: Center(
              child: Text(
                total,
                style: TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                  color: isDark ? Colors.white : const Color(0xFF1E293B),
                ),
              ),
            ),
          ),

          // 4. %
          SizedBox(
            width: kColPct,
            child: Center(
              child: Text(
                '$pct%',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  fontSize: 12.5,
                  color: isDark ? Colors.white70 : const Color(0xFF334155),
                ),
              ),
            ),
          ),

          // 5. GPA
          SizedBox(
            width: kColGpa,
            child: Center(
              child: Text(
                gpa,
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 12.5,
                  color: gpa == '0.00' ? AppTheme.red : AppTheme.primary,
                ),
              ),
            ),
          ),

          // 6. Grade Badge
          SizedBox(
            width: kColGrade,
            child: Center(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                decoration: BoxDecoration(
                  color: gradeColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: gradeColor.withOpacity(0.3), width: 0.8),
                ),
                child: Text(
                  grade,
                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 11.5, color: gradeColor),
                ),
              ),
            ),
          ),

          // 7. Marksheet A4 Preview Button
          SizedBox(
            width: kColMarksheet,
            child: Center(
              child: InkWell(
                onTap: () => _openReportCardModal(s),
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF7C3AED).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFF7C3AED).withOpacity(0.3), width: 0.8),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.print_outlined, size: 12, color: Color(0xFF7C3AED)),
                      SizedBox(width: 4),
                      Text('A4', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF7C3AED))),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- Mobile Card List View ---
  Widget _buildCardListView(List<Map<String, dynamic>> marksList, bool isDark) {
    if (marksList.isEmpty) {
      if (_isLoading) {
        return Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const SizedBox(
                  width: 38,
                  height: 38,
                  child: CircularProgressIndicator(strokeWidth: 3, color: AppTheme.primary),
                ),
                const SizedBox(height: 18),
                const Text(
                  'Loading Marks Ledger...',
                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Fetching students and academic records from server.\nPlease wait a moment...',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 11.5, color: AppTheme.darkSubtext),
                ),
              ],
            ),
          ),
        );
      }
      return const Center(child: Text('No students match filter'));
    }

    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
      cacheExtent: 600,
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 90),
      itemCount: marksList.length,
      itemBuilder: (ctx, i) {
        final item = marksList[i];
        final grade = item['grade'] as String;
        final gpa = item['gpa'] as String;
        final pct = (item['percentage'] as num).toStringAsFixed(1);
        final gradeColor = _getGradeColor(grade);

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkCard : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
              width: 1,
            ),
          ),
          child: Column(
            children: [
              // Header
              Row(
                children: [
                  Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: gradeColor.withOpacity(0.15),
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        '${item['roll_no']}',
                        style: TextStyle(color: gradeColor, fontWeight: FontWeight.w900, fontSize: 13),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item['name'] as String, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                        Text('Roll #${item['roll_no']} • GPA: $gpa • $pct%', style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext)),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: gradeColor.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: gradeColor),
                    ),
                    child: Text(grade, style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: gradeColor)),
                  ),
                  const SizedBox(width: 6),
                  IconButton(
                    icon: const Icon(Icons.description_outlined, size: 18, color: AppTheme.primary),
                    tooltip: 'View Marksheet',
                    onPressed: () => _openReportCardModal(item),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              // Inputs Grid
              if (_isAllSubjectsMode) ...[
                LayoutBuilder(
                  builder: (context, constraints) {
                    final double availWidth = constraints.maxWidth;
                    // On wide screens (tablets) 3 columns, on mobile screens 2 columns
                    final int crossAxisCount = availWidth > 480 ? 3 : 2;
                    const double spacing = 8.0;
                    final double itemWidth = (availWidth - (crossAxisCount - 1) * spacing) / crossAxisCount;

                    return Wrap(
                      spacing: spacing,
                      runSpacing: 10,
                      children: _masterSubjects.map((sub) {
                        final sName = sub['name'] as String;
                        final controllers = item['subjectControllers'] as Map<String, TextEditingController>?;
                        final ctrl = controllers?[sName];
                        final bool hasPr = (sub['has_practical'] as bool?) ?? false;
                        final fm = (sub['fm'] as num?)?.toInt() ?? 100;

                        return SizedBox(
                          width: itemWidth,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      sName, // Show full subject name!
                                      style: TextStyle(
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w700,
                                        color: isDark ? Colors.white : const Color(0xFF1E293B),
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    hasPr
                                        ? 'TH:${(sub['th_fm'] ?? 75).toInt()}+PR:${(sub['pr_fm'] ?? 25).toInt()}'
                                        : 'FM:$fm',
                                    style: TextStyle(
                                      fontSize: 9,
                                      fontWeight: FontWeight.w600,
                                      color: hasPr ? AppTheme.primary : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 3),
                              SizedBox(
                                height: 35,
                                child: TextField(
                                  controller: ctrl,
                                  keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                                  textInputAction: TextInputAction.next,
                                  enableSuggestions: false,
                                  autocorrect: false,
                                  enableInteractiveSelection: false,
                                  scrollPadding: EdgeInsets.zero,
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                                  decoration: InputDecoration(
                                    isDense: true,
                                    hintText: '—',
                                    hintStyle: TextStyle(
                                      color: isDark ? Colors.white24 : AppTheme.darkSubtext.withOpacity(0.35),
                                      fontSize: 12,
                                    ),
                                    contentPadding: const EdgeInsets.symmetric(vertical: 7, horizontal: 6),
                                    fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                                    filled: true,
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(14),
                                      borderSide: BorderSide(
                                        color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                                        width: 1.1,
                                      ),
                                    ),
                                    enabledBorder: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(14),
                                      borderSide: BorderSide(
                                        color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                                        width: 1.1,
                                      ),
                                    ),
                                    focusedBorder: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(14),
                                      borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.8),
                                    ),
                                  ),
                                  onChanged: (_) => _recalculateRow(i),
                                ),
                              ),
                            ],
                          ),
                        );
                      }).toList(),
                    );
                  },
                ),
              ] else ...[
                if (_currentSubjectHasPractical) ...[
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: item['theory'] as TextEditingController,
                          keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                          textInputAction: TextInputAction.next,
                          enableSuggestions: false,
                          autocorrect: false,
                          enableInteractiveSelection: false,
                          scrollPadding: EdgeInsets.zero,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                          decoration: InputDecoration(
                            labelText: '$_selectedSubject - Theory (${_getCurrentSubjectThFm().toInt()})',
                            labelStyle: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700),
                            hintText: '0 - ${_getCurrentSubjectThFm().toInt()}',
                            isDense: true,
                            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                            filled: true,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(
                                color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                                width: 1.1,
                              ),
                            ),
                          ),
                          onChanged: (_) => _recalculateRow(i),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: TextField(
                          controller: item['practical'] as TextEditingController,
                          keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                          textInputAction: TextInputAction.next,
                          enableSuggestions: false,
                          autocorrect: false,
                          enableInteractiveSelection: false,
                          scrollPadding: EdgeInsets.zero,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                          decoration: InputDecoration(
                            labelText: '$_selectedSubject - Practical (${_getCurrentSubjectPrFm().toInt()})',
                            labelStyle: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700),
                            hintText: '0 - ${_getCurrentSubjectPrFm().toInt()}',
                            isDense: true,
                            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                            filled: true,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(
                                color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                                width: 1.1,
                              ),
                            ),
                          ),
                          onChanged: (_) => _recalculateRow(i),
                        ),
                      ),
                    ],
                  ),
                ] else ...[
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: item['mark'] as TextEditingController,
                          keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: false),
                          textInputAction: TextInputAction.next,
                          enableSuggestions: false,
                          autocorrect: false,
                          enableInteractiveSelection: false,
                          scrollPadding: EdgeInsets.zero,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                          decoration: InputDecoration(
                            labelText: '$_selectedSubject (FM: ${_getCurrentSubjectFm().toInt()} | PM: ${_getCurrentSubjectPm().toInt()})',
                            labelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
                            hintText: 'Enter mark (0 - ${_getCurrentSubjectFm().toInt()})',
                            isDense: true,
                            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                            filled: true,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(14),
                              borderSide: BorderSide(
                                color: isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1),
                                width: 1.1,
                              ),
                            ),
                          ),
                          onChanged: (_) {
                            _syncMasterFromSingleSubject(i, _selectedSubject);
                            _recalculateRow(i);
                          },
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ],
          ),
        );
      },
    );
  }

  // --- Helper Widgets ---
  Widget _buildToolbarButton({
    required IconData icon,
    required String label,
    required Color color,
    Color? backgroundColor,
    Color? borderColor,
    Color? textColor,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    final fg = textColor ?? color;
    final bg = backgroundColor ?? color.withOpacity(isDark ? 0.18 : 0.1);
    final border = borderColor ?? color.withOpacity(0.35);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: border, width: 1.0),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: fg),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: fg),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSelector(
    String label,
    String value,
    List<String> items,
    ValueChanged<String?> onChanged,
    bool isDark, {
    Map<String, String>? displayMap,
  }) {
    final uniqueItems = items.toSet().toList();
    return DropdownButtonFormField<String>(
      value: uniqueItems.contains(value) ? value : (uniqueItems.isNotEmpty ? uniqueItems.first : null),
      isExpanded: true,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(fontSize: 10.5, color: AppTheme.darkSubtext, fontWeight: FontWeight.w700),
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        filled: true,
        fillColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide.none,
        ),
      ),
      items: uniqueItems
          .map((item) => DropdownMenuItem(
                value: item,
                child: Text(
                  displayMap != null && displayMap.containsKey(item) ? displayMap[item]! : item,
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
              ))
          .toList(),
      onChanged: onChanged,
    );
  }
}
