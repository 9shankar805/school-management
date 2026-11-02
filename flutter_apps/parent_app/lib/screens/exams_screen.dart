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
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Exams & Assessments'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.badge_outlined),
            tooltip: 'View Admit Card',
            onPressed: () {
              if (nextExam != null) {
                _showAdmitCardModal(context, child, nextExam);
              }
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Next Exam Hero Countdown
            if (nextExam != null)
              GradientCard(
                colors: const [Color(0xFFEA580C), Color(0xFFF97316)],
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.25),
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
                            color: Colors.black.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.timer_outlined, color: Colors.white, size: 12),
                              const SizedBox(width: 4),
                              Text(
                                '${nextExam.daysLeft} days remaining',
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
                              ),
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
                            color: Colors.white.withValues(alpha: 0.2),
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
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side: const BorderSide(color: Colors.white60),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () => _showAdmitCardModal(context, child, nextExam),
                        icon: const Icon(Icons.download, size: 16),
                        label: const Text('Download Digital Admit Card', style: TextStyle(fontWeight: FontWeight.w800)),
                      ),
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
                  '${child.grade} - Section "${child.section}"',
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
              ...exams.map((ex) => _ExamCard(
                    exam: ex,
                    onTap: () => _showExamDetailSheet(context, child, ex),
                  )),
          ],
        ),
      ),
    );
  }

  void _showAdmitCardModal(BuildContext context, dynamic child, ExamItem exam) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.all(20),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E3A8A).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.school, color: Color(0xFF1E3A8A), size: 24),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('OFFICIAL ADMIT CARD', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF1E3A8A))),
                      Text('Terminal Examination 2081 • NEB Affiliated', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            _AdmitLine(label: 'Candidate Name', value: child.name),
            _AdmitLine(label: 'Class & Section', value: '${child.grade} - ${child.section}'),
            _AdmitLine(label: 'Symbol / Roll No', value: child.rollNo),
            const _AdmitLine(label: 'Exam Center', value: 'Main Academic Block (Hall 302)'),
            const _AdmitLine(label: 'Assigned Desk', value: 'Desk #14 (Row C)'),
            const _AdmitLine(label: 'Reporting Time', value: '09:30 AM (Strict)'),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: const Text(
                '⚠️ Candidate must present this digital or printed pass along with School ID Card. Electronic devices are strictly prohibited.',
                style: TextStyle(fontSize: 10, color: Color(0xFF92400E), fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Downloading Admit Card for ${child.name}...'),
                  backgroundColor: const Color(0xFF047857),
                ),
              );
            },
            icon: const Icon(Icons.download, size: 16),
            label: const Text('Save PDF Pass'),
          ),
        ],
      ),
    );
  }

  void _showExamDetailSheet(BuildContext context, dynamic child, ExamItem exam) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(exam.subject, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.orange.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${exam.daysLeft}d left',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.orange),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text('${exam.type} • Full Marks: ${exam.totalMarks}', style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  _ExamDetailRow(icon: Icons.calendar_month, label: 'Date', value: exam.date),
                  const Divider(height: 12),
                  _ExamDetailRow(icon: Icons.access_time, label: 'Time', value: exam.time),
                  const Divider(height: 12),
                  _ExamDetailRow(icon: Icons.meeting_room, label: 'Exam Room', value: exam.room),
                ],
              ),
            ),
            const SizedBox(height: 14),
            const Text('Tested Syllabus & Chapters:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            Text(
              exam.syllabus,
              style: const TextStyle(fontSize: 12, color: AppTheme.dark, height: 1.3),
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    child: const Text('Close'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(ctx);
                      _showAdmitCardModal(context, child, exam);
                    },
                    icon: const Icon(Icons.badge_outlined, size: 16),
                    label: const Text('Admit Card'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _ExamCard extends StatelessWidget {
  final ExamItem exam;
  final VoidCallback onTap;

  const _ExamCard({required this.exam, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.12),
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
                    color: AppTheme.orange.withValues(alpha: 0.12),
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
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Syllabus: ${exam.syllabus}',
                      style: const TextStyle(fontSize: 11, color: AppTheme.dark, fontStyle: FontStyle.italic),
                    ),
                  ),
                  const Icon(Icons.chevron_right, size: 14, color: AppTheme.grey),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AdmitLine extends StatelessWidget {
  final String label, value;
  const _AdmitLine({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w600)),
          Text(value, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
        ],
      ),
    );
  }
}

class _ExamDetailRow extends StatelessWidget {
  final IconData icon;
  final String label, value;

  const _ExamDetailRow({required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppTheme.primary),
        const SizedBox(width: 8),
        Text(label, style: const TextStyle(fontSize: 11.5, color: AppTheme.grey, fontWeight: FontWeight.w600)),
        const Spacer(),
        Text(value, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
      ],
    );
  }
}
