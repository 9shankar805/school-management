import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../widgets/common_widgets.dart';
import '../providers/teacher_provider.dart';

class SeatingChartScreen extends StatefulWidget {
  const SeatingChartScreen({super.key});
  @override
  State<SeatingChartScreen> createState() => _SeatingChartScreenState();
}

class _SeatingChartScreenState extends State<SeatingChartScreen> {
  int _selectedClass = 0;
  int? _selectedSeat;
  int? _dragSource;

  // 5x6 grid, null = empty seat
  late List<String?> _seats;
  bool _initialized = false;

  void _initSeats(List<Student> students) {
    _seats = List.filled(30, null);
    final names = students.map((s) => s.name.split(' ').first).toList();
    for (int i = 0; i < names.length && i < 30; i++) {
      _seats[i] = names[i];
    }
  }

  void _swapSeats(int a, int b) {
    setState(() {
      final tmp = _seats[a];
      _seats[a] = _seats[b];
      _seats[b] = tmp;
    });
  }

  @override
  Widget build(BuildContext context) {
    final teacherProvider = Provider.of<TeacherProvider>(context);
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

    if (!_initialized) {
      _initSeats(activeStudents);
      _initialized = true;
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Seating Chart'),
        leading: CircleIconButton(icon: Icons.arrow_back_ios_new,
            onTap: () => Navigator.pop(context), size: 38),
        leadingWidth: 60,
        actions: [
          CircleIconButton(icon: Icons.shuffle_rounded,
              onTap: _randomize, size: 38),
          const SizedBox(width: 8),
          CircleIconButton(icon: Icons.save_outlined, onTap: () {}, size: 38),
          const SizedBox(width: 16),
        ],
      ),
      body: Column(children: [
        // Class selector
        SizedBox(
          height: 44,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: classesList.length,
            itemBuilder: (_, i) {
              final sel = i == _selectedClass;
              return GestureDetector(
                onTap: () => setState(() {
                  _selectedClass = i;
                  _initSeats(activeStudents);
                }),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    gradient: sel ? const LinearGradient(
                        colors: [AppTheme.primary, AppTheme.secondary]) : null,
                    color: sel ? null : (Theme.of(context).brightness == Brightness.dark
                        ? AppTheme.darkCard : Colors.white),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(classesList[i].section,
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600,
                          color: sel ? Colors.white : AppTheme.grey)),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 12),
        // Teacher desk
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 20),
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
                colors: [AppTheme.primary, AppTheme.secondary]),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Center(child: Text('🖥  Teacher\'s Desk',
              style: TextStyle(color: Colors.white,
                  fontWeight: FontWeight.w700, fontSize: 13))),
        ),
        const SizedBox(height: 16),
        // Seating grid
        Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: GridView.builder(
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 5,
                crossAxisSpacing: 8,
                mainAxisSpacing: 8,
                childAspectRatio: 0.85,
              ),
              itemCount: 30,
              itemBuilder: (_, i) {
                final name = _seats[i];
                final isSelected = _selectedSeat == i;
                return GestureDetector(
                  onTap: () {
                    if (_selectedSeat == null) {
                      setState(() => _selectedSeat = i);
                    } else {
                      _swapSeats(_selectedSeat!, i);
                      setState(() => _selectedSeat = null);
                    }
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    decoration: BoxDecoration(
                      color: name == null
                          ? AppTheme.lightBg
                          : isSelected
                              ? AppTheme.primary
                              : Color(cls.colorHex),
                      borderRadius: BorderRadius.circular(12),
                      border: isSelected
                          ? Border.all(color: AppTheme.primary, width: 2)
                          : null,
                      boxShadow: name != null ? [BoxShadow(
                          color: Colors.black.withOpacity(0.06),
                          blurRadius: 6, offset: const Offset(0, 2))] : null,
                    ),
                    child: name == null
                        ? Center(child: Icon(Icons.add,
                            color: AppTheme.grey.withOpacity(0.3), size: 20))
                        : Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                            Container(
                              width: 32, height: 32,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: isSelected
                                    ? Colors.white24
                                    : AppTheme.primary.withOpacity(0.15),
                              ),
                              child: Center(child: Text(name[0],
                                  style: TextStyle(
                                      fontWeight: FontWeight.w800, fontSize: 14,
                                      color: isSelected ? Colors.white : AppTheme.primary))),
                            ),
                            const SizedBox(height: 4),
                            Text(name,
                                textAlign: TextAlign.center,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(fontSize: 9, fontWeight: FontWeight.w600,
                                    color: isSelected ? Colors.white : AppTheme.dark)),
                          ]),
                  ),
                ).animate().scale(begin: const Offset(0.9, 0.9),
                    duration: 200.ms, delay: (i * 20).ms);
              },
            ),
          ),
        ),
        // Legend
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 16),
          child: Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            const Icon(Icons.touch_app_outlined, size: 14, color: AppTheme.grey),
            const SizedBox(width: 6),
            const Text('Tap a seat to select, tap another to swap',
                style: TextStyle(fontSize: 11, color: AppTheme.grey)),
          ]),
        ),
      ]),
    );
  }

  void _randomize() {
    final filled = _seats.where((s) => s != null).toList()..shuffle();
    int j = 0;
    setState(() {
      for (int i = 0; i < _seats.length; i++) {
        if (_seats[i] != null) _seats[i] = filled[j++];
      }
    });
  }
}
