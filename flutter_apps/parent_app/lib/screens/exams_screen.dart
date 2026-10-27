import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class ExamsScreen extends StatefulWidget {
  const ExamsScreen({super.key});
  @override
  State<ExamsScreen> createState() => _ExamsScreenState();
}

class _ExamsScreenState extends State<ExamsScreen> {
  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final exams = api.exams;
    final child = api.selectedChild;

    final nextExam = exams.isNotEmpty ? exams.first : null;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Exams & Assessments'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Next Exam Hero Countdown
            if (nextExam != null)
              GradientCard(
                colors: const [Color(0xFFFF9F1A), Color(0xFFFF6B6B)],
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.25),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            nextExam.type.toUpperCase(),
                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w900),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.timer_outlined, color: Colors.white, size: 12),
                              const SizedBox(width: 4),
                              Text('${nextExam.daysLeft} days remaining', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                            ],
                          ),
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
                              Text(
                                nextExam.subject,
                                style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w900),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${nextExam.date} • ${nextExam.time}',
                                style: const TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w600),
                              ),
                              Text(
                                'Exam Hall: ${nextExam.room}',
                                style: const TextStyle(color: Colors.white70, fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            shape: BoxShape.circle,
                          ),
                          child: Text(
                            '${nextExam.daysLeft}\nDAYS',
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 24),

            // Timetable Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const SectionHeader(title: '📅 Examination Schedule'),
                Text(
                  '${child.grade} - ${child.section}',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.primary),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (exams.isEmpty)
              const EmptyStateWidget(
                icon: Icons.quiz_outlined,
                title: 'No upcoming exams',
                subtitle: 'Exam routines will be published here as soon as announced.',
              )
            else
              ...exams.map((ex) => _ExamCard(exam: ex)),
          ],
        ),
      ),
    );
  }
}

class _ExamCard extends StatelessWidget {
  final ExamItem exam;
  const _ExamCard({required this.exam});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.school_outlined, color: AppTheme.primary, size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(exam.subject, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                      Text(
                        '${exam.type} • Total: ${exam.totalMarks} Marks',
                        style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.orange.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${exam.daysLeft}d left',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.orange),
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.calendar_month_outlined, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text(exam.date, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                  ],
                ),
                Row(
                  children: [
                    const Icon(Icons.access_time, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text(exam.time, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                  ],
                ),
                Row(
                  children: [
                    const Icon(Icons.meeting_room_outlined, size: 14, color: AppTheme.grey),
                    const SizedBox(width: 4),
                    Text(exam.room, style: const TextStyle(fontSize: 11, color: AppTheme.primary, fontWeight: FontWeight.w700)),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 10),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppTheme.lightBg,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                'Syllabus: ${exam.syllabus}',
                style: const TextStyle(fontSize: 11, color: AppTheme.dark, fontStyle: FontStyle.italic),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
