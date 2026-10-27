import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../widgets/common_widgets.dart';
import 'report_card_screen.dart';

class GradesScreen extends StatefulWidget {
  const GradesScreen({super.key});
  @override
  State<GradesScreen> createState() => _GradesScreenState();
}

class _GradesScreenState extends State<GradesScreen> {
  String _selectedTerm = 'Second Term Examination';

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final subjects = api.subjects;

    final double avgScore = subjects.isNotEmpty
        ? subjects.fold<double>(0.0, (sum, s) => sum + s.score) / subjects.length
        : 88.0;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Academic Grades'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.description_outlined),
            tooltip: 'View Report Card',
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ReportCardScreen()),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Term Selector Dropdown
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE5E9F0)),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _selectedTerm,
                  isExpanded: true,
                  icon: const Icon(Icons.keyboard_arrow_down_rounded, color: AppTheme.primary),
                  items: const [
                    DropdownMenuItem(value: 'Second Term Examination', child: Text('Second Term Examination 2081')),
                    DropdownMenuItem(value: 'First Term Examination', child: Text('First Term Examination 2081')),
                    DropdownMenuItem(value: 'Unit Assessment 3', child: Text('Unit Assessment 3')),
                  ],
                  onChanged: (val) {
                    if (val != null) setState(() => _selectedTerm = val);
                  },
                ),
              ),
            ),
            const SizedBox(height: 16),

            // GPA Hero Card
            GradientCard(
              colors: const [Color(0xFF2E86DE), Color(0xFF6C3CE9)],
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            child.name,
                            style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 2),
                          const Text(
                            'Cumulative GPA',
                            style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Text('Grade A+', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _GpaStatBox(label: 'GPA Score', value: child.gpa.toStringAsFixed(2)),
                      _GpaStatBox(label: 'Average Score', value: '${avgScore.toInt()}%'),
                      _GpaStatBox(label: 'Total Subjects', value: '${subjects.length}'),
                      _GpaStatBox(label: 'Class Rank', value: '#${child.rank}'),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 20),

            // Button to View Official Report Card
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ReportCardScreen()),
                ),
                icon: const Icon(Icons.file_download_outlined, color: Colors.white),
                label: const Text('Download Official Report Card (PDF)'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Subject-wise Breakdown
            const SectionHeader(title: '📊 Subject Breakdown'),
            const SizedBox(height: 12),
            ...subjects.map((sub) => _DetailedSubjectCard(subject: sub)),
          ],
        ),
      ),
    );
  }
}

class _GpaStatBox extends StatelessWidget {
  final String label, value;
  const _GpaStatBox({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(value, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _DetailedSubjectCard extends StatelessWidget {
  final SubjectGrade subject;
  const _DetailedSubjectCard({required this.subject});

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
                    color: Color(subject.colorHex).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(subjectIcon(subject.iconCode), color: Color(subject.colorHex), size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(subject.name, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                      Text(
                        '${subject.code} • ${subject.teacher}',
                        style: const TextStyle(fontSize: 11, color: AppTheme.grey),
                      ),
                    ],
                  ),
                ),
                GradeBadge(grade: subject.grade),
              ],
            ),
            const SizedBox(height: 12),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: subject.progress,
                backgroundColor: AppTheme.greyLight,
                valueColor: AlwaysStoppedAnimation<Color>(Color(subject.colorHex)),
                minHeight: 8,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Score: ${subject.score.toInt()} / ${subject.maxScore.toInt()}',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
                ),
                Text(
                  subject.remarks,
                  style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontStyle: FontStyle.italic),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
