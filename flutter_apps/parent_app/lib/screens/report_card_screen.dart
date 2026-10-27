import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';

class ReportCardScreen extends StatefulWidget {
  const ReportCardScreen({super.key});
  @override
  State<ReportCardScreen> createState() => _ReportCardScreenState();
}

class _ReportCardScreenState extends State<ReportCardScreen> {
  int _selectedCardIndex = 0;

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final reportCards = api.reportCards.isNotEmpty ? api.reportCards : [
      ReportCard(
        id: 1,
        term: 'Second Terminal Examination 2081',
        academicYear: '2081-2082 (2026)',
        issueDate: '2026-08-15',
        overallGrade: 'A+',
        principalRemarks: 'Outstanding intellectual performance with exemplary discipline.',
        gpa: 3.9,
        percentage: 91.5,
        totalMarks: 700,
        obtainedMarks: 641,
        rank: 2,
        totalStudents: 42,
        subjectGrades: api.subjects,
      ),
    ];

    final card = reportCards[_selectedCardIndex.clamp(0, reportCards.length - 1)];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Terminal Report Card'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.share_outlined),
            tooltip: 'Share Marksheet',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Sharing official marksheet PDF...')),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.print_outlined),
            tooltip: 'Print Marksheet',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Sent marksheet to air printer...')),
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          children: [
            // Term Selector Chips
            if (reportCards.length > 1)
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: reportCards.asMap().entries.map((e) {
                    final isSel = e.key == _selectedCardIndex;
                    return GestureDetector(
                      onTap: () => setState(() => _selectedCardIndex = e.key),
                      child: Container(
                        margin: const EdgeInsets.only(right: 10),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        decoration: BoxDecoration(
                          color: isSel ? AppTheme.primary : Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: isSel ? AppTheme.primary : const Color(0xFFE5E9F0)),
                        ),
                        child: Text(
                          e.value.term,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: isSel ? Colors.white : AppTheme.dark,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            const SizedBox(height: 16),

            // Official Marksheet Sheet
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xFFE5E9F0), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.06),
                    blurRadius: 20,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // School crest & heading
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withOpacity(0.12),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.school, color: AppTheme.primary, size: 28),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    child.schoolName.toUpperCase(),
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, letterSpacing: 0.5),
                  ),
                  const Text(
                    'Affiliated with National Examination Board (NEB), Nepal',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 10, color: AppTheme.grey),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryLight,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      'OFFICIAL PERFORMANCE TRANSCRIPT — ${card.academicYear}',
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.primaryDark),
                    ),
                  ),
                  const Divider(height: 24, thickness: 1.2),

                  // Student Meta Grid
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _MetaRow(label: 'Student Name', value: child.name),
                            _MetaRow(label: 'Grade & Section', value: '${child.grade} - ${child.section}'),
                            _MetaRow(label: 'Roll Number', value: child.rollNo),
                          ],
                        ),
                      ),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _MetaRow(label: 'Admission ID', value: child.admissionNo),
                            _MetaRow(label: 'Term Exam', value: card.term.split(' ').first),
                            _MetaRow(label: 'Date of Issue', value: card.issueDate),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Subject Table Header
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: AppTheme.lightBg,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Row(
                      children: [
                        Expanded(flex: 4, child: Text('Subject', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800))),
                        Expanded(flex: 2, child: Text('Max', textAlign: TextAlign.center, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800))),
                        Expanded(flex: 2, child: Text('Score', textAlign: TextAlign.center, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800))),
                        Expanded(flex: 2, child: Text('Grade', textAlign: TextAlign.center, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800))),
                      ],
                    ),
                  ),
                  const SizedBox(height: 6),

                  // Subject Table Rows
                  ...card.subjectGrades.map((sub) {
                    return Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                      child: Row(
                        children: [
                          Expanded(
                            flex: 4,
                            child: Text(
                              sub.name,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                            ),
                          ),
                          Expanded(
                            flex: 2,
                            child: Text(
                              '${sub.maxScore.toInt()}',
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontSize: 12, color: AppTheme.grey),
                            ),
                          ),
                          Expanded(
                            flex: 2,
                            child: Text(
                              '${sub.score.toInt()}',
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
                            ),
                          ),
                          Expanded(
                            flex: 2,
                            child: Center(child: GradeBadge(grade: sub.grade)),
                          ),
                        ],
                      ),
                    );
                  }),
                  const Divider(height: 24, thickness: 1.2),

                  // Results Summary Block
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      gradient: AppTheme.primaryGradient,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _ResultStat(label: 'Total Marks', value: '${card.obtainedMarks}/${card.totalMarks}'),
                        _ResultStat(label: 'Percentage', value: '${card.percentage}%'),
                        _ResultStat(label: 'Final GPA', value: card.gpa.toStringAsFixed(2)),
                        _ResultStat(label: 'Class Rank', value: '#${card.rank}'),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Principal Remarks
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppTheme.lightBg,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Principal & Educator Remarks:', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.grey)),
                        const SizedBox(height: 4),
                        Text(
                          '"${card.principalRemarks}"',
                          style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Signatures
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: const [
                      _SignBlock(title: 'Class Teacher'),
                      _SignBlock(title: 'Exam Controller'),
                      _SignBlock(title: 'Principal / Seal'),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().scale(begin: const Offset(0.96, 0.96)),
          ],
        ),
      ),
    );
  }
}

class _MetaRow extends StatelessWidget {
  final String label, value;
  const _MetaRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 9, color: AppTheme.grey, fontWeight: FontWeight.w600)),
          Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
        ],
      ),
    );
  }
}

class _ResultStat extends StatelessWidget {
  final String label, value;
  const _ResultStat({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 9, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _SignBlock extends StatelessWidget {
  final String title;
  const _SignBlock({required this.title});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          width: 80,
          height: 1,
          color: AppTheme.grey.withOpacity(0.4),
        ),
        const SizedBox(height: 4),
        Text(title, style: const TextStyle(fontSize: 9, color: AppTheme.grey, fontWeight: FontWeight.w700)),
      ],
    );
  }
}
