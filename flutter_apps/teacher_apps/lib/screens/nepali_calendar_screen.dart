import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';

class NepaliCalendarScreen extends StatefulWidget {
  const NepaliCalendarScreen({super.key});

  @override
  State<NepaliCalendarScreen> createState() => _NepaliCalendarScreenState();
}

class _NepaliCalendarScreenState extends State<NepaliCalendarScreen> {
  String _selectedMonth = 'Ashwin 2081 (असोज)';

  final List<String> _bsMonths = [
    'Baisakh 2081 (बैशाख)',
    'Jestha 2081 (जेठ)',
    'Ashadh 2081 (असार)',
    'Shrawan 2081 (साउन)',
    'Bhadra 2081 (भदौ)',
    'Ashwin 2081 (असोज)',
    'Kartik 2081 (कार्तिक)',
    'Mangsir 2081 (मंसिर)',
    'Poush 2081 (पुष)',
    'Magh 2081 (माघ)',
    'Falgun 2081 (फागुन)',
    'Chaitra 2081 (चैत)',
  ];

  final List<Map<String, dynamic>> _upcomingEvents = [
    {
      'date': 'Ashwin 10 (Sep 26 - Today)',
      'title': 'Faculty Evaluation & Lesson Diary Review',
      'type': 'Academic Work',
      'color': AppTheme.primary,
      'isHoliday': false,
    },
    {
      'date': 'Ashwin 17 (Oct 03)',
      'title': 'Ghatasthapana (घटस्थापना / बडादशैं प्रारम्भ)',
      'type': 'Public Holiday',
      'color': AppTheme.red,
      'isHoliday': true,
    },
    {
      'date': 'Ashwin 24 - Kartik 02 (Oct 10 - 18)',
      'title': 'Dashain Vacation 2081 (विजयादशमी बिदा)',
      'type': 'School Vacation',
      'color': AppTheme.red,
      'isHoliday': true,
    },
    {
      'date': 'Kartik 15 - 19 (Oct 31 - Nov 04)',
      'title': 'Tihar, Laxmi Puja & Bhai Tika (तिहार बिदा)',
      'type': 'Festival Holiday',
      'color': AppTheme.orange,
      'isHoliday': true,
    },
    {
      'date': 'Kartik 22 (Nov 07)',
      'title': 'Chhath Puja (छठ पर्व)',
      'type': 'Public Holiday',
      'color': AppTheme.red,
      'isHoliday': true,
    },
    {
      'date': 'Mangsir 10 - 18',
      'title': 'Second Terminal Examination 2081',
      'type': 'Term Exams',
      'color': AppTheme.primary,
      'isHoliday': false,
    },
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Nepali Patro (नेपाली पात्रो)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Bikram Sambat 2081 • School Academic Calendar',
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
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(18, 4, 18, 90),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Month Selector
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
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
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _selectedMonth,
                  isExpanded: true,
                  icon: const Icon(Icons.keyboard_arrow_down_rounded, color: AppTheme.primary),
                  dropdownColor: isDark ? AppTheme.darkCard : Colors.white,
                  items: _bsMonths
                      .map((m) => DropdownMenuItem(
                            value: m,
                            child: Text(
                              m,
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5),
                            ),
                          ))
                      .toList(),
                  onChanged: (v) => setState(() => _selectedMonth = v!),
                ),
              ),
            ),
            const SizedBox(height: 14),

            // Calendar Visual Grid
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(22),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Days Header (Sun to Sat)
                  const Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _DayHeader('आइत', isWeekend: false),
                      _DayHeader('सोम', isWeekend: false),
                      _DayHeader('मंगल', isWeekend: false),
                      _DayHeader('बुध', isWeekend: false),
                      _DayHeader('बिही', isWeekend: false),
                      _DayHeader('शुक्र', isWeekend: false),
                      _DayHeader('शनि', isWeekend: true),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Divider(
                    height: 1,
                    color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  ),
                  const SizedBox(height: 12),

                  // Calendar Grid numbers (1 to 30)
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 7,
                      mainAxisSpacing: 8,
                      crossAxisSpacing: 8,
                      childAspectRatio: 1,
                    ),
                    itemCount: 30,
                    itemBuilder: (ctx, idx) {
                      final dayNum = idx + 1;
                      final isSaturday = (idx + 1) % 7 == 0;
                      final isHoliday = isSaturday || dayNum == 17 || (dayNum >= 24 && dayNum <= 28);
                      final isToday = dayNum == 10;

                      return Container(
                        decoration: BoxDecoration(
                          gradient: isToday
                              ? const LinearGradient(
                                  colors: [Color(0xFF0F4C81), Color(0xFF2563EB)],
                                )
                              : null,
                          color: isToday
                              ? null
                              : (isHoliday
                                  ? (isDark ? AppTheme.red.withOpacity(0.15) : const Color(0xFFFEF2F2))
                                  : (isDark ? AppTheme.darkBg : AppTheme.lightBg)),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: isToday
                                ? AppTheme.primary
                                : (isHoliday
                                    ? AppTheme.red.withOpacity(0.3)
                                    : (isDark ? AppTheme.darkCardBorder : Colors.transparent)),
                            width: 1,
                          ),
                        ),
                        child: Center(
                          child: Text(
                            '$dayNum',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: isToday || isHoliday ? FontWeight.w900 : FontWeight.w700,
                              color: isToday
                                  ? Colors.white
                                  : (isHoliday ? AppTheme.red : (isDark ? Colors.white : AppTheme.dark)),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05, end: 0),
            const SizedBox(height: 20),

            // Holidays & School Events
            const SectionHeader(
              title: 'Holidays & School Events (२०८१)',
              subtitle: 'Academic deadlines, festivals and public breaks',
            ),
            const SizedBox(height: 10),
            ..._upcomingEvents.map((evt) {
              final color = evt['color'] as Color;
              final isHoliday = evt['isHoliday'] == true;

              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkCard : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isHoliday
                        ? color.withOpacity(0.3)
                        : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                    width: 1,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(isDark ? 0.2 : 0.02),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        isHoliday ? Icons.celebration_rounded : Icons.event_note_rounded,
                        color: color,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            evt['title'] as String,
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            evt['date'] as String,
                            style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        evt['type'] as String,
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: color),
                      ),
                    ),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }
}

class _DayHeader extends StatelessWidget {
  final String label;
  final bool isWeekend;
  const _DayHeader(this.label, {required this.isWeekend});

  @override
  Widget build(BuildContext context) {
    return Text(
      label,
      style: TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w800,
        color: isWeekend ? AppTheme.red : AppTheme.darkSubtext,
      ),
    );
  }
}
