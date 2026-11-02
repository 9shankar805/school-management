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
  String _selectedTerm = 'Second Term Examination 2081';

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final subjects = api.subjects;

    final double avgScore = subjects.isNotEmpty
        ? subjects.fold<double>(0.0, (sum, s) => sum + s.score) / subjects.length
        : 88.0;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Academic Grades & GPA'),
        leading: CircleIconButton(
          icon: Icons.arrow_back_ios_new,
          onTap: () => Navigator.pop(context),
          size: 38,
        ),
        leadingWidth: 60,
        actions: [
          IconButton(
            icon: const Icon(Icons.info_outline),
            tooltip: 'CDC Grading Guide',
            onPressed: () => _showCdcGradingGuide(context),
          ),
          IconButton(
            icon: const Icon(Icons.description_outlined),
            tooltip: 'View Official Marksheet',
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ReportCardScreen()),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 10, 20, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Term Selector Dropdown
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0), width: 1.2),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _selectedTerm,
                  isExpanded: true,
                  icon: const Icon(Icons.keyboard_arrow_down_rounded, color: AppTheme.primary),
                  items: const [
                    DropdownMenuItem(
                      value: 'Second Term Examination 2081',
                      child: Text('Second Term Examination 2081 (Current)', style: TextStyle(fontWeight: FontWeight.w700)),
                    ),
                    DropdownMenuItem(
                      value: 'First Term Examination 2081',
                      child: Text('First Term Examination 2081', style: TextStyle(fontWeight: FontWeight.w700)),
                    ),
                    DropdownMenuItem(
                      value: 'Periodic Assessment 3',
                      child: Text('Periodic Unit Assessment 3', style: TextStyle(fontWeight: FontWeight.w700)),
                    ),
                  ],
                  onChanged: (val) {
                    if (val != null) setState(() => _selectedTerm = val);
                  },
                ),
              ),
            ),
            const SizedBox(height: 16),

            // GPA Hero Card with Gradient
            GradientCard(
              colors: const [Color(0xFF1E3A8A), Color(0xFF3B82F6)],
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
                            style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w900),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.22),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: const Text('Grade A+', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 14)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _GpaStatBox(label: 'GPA Score', value: child.gpa.toStringAsFixed(2)),
                      _GpaStatBox(label: 'Average Score', value: '${avgScore.toInt()}%'),
                      _GpaStatBox(label: 'Total Subjects', value: '${subjects.length}'),
                      _GpaStatBox(label: 'Class Rank', value: '#${child.rank} of 42'),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
            const SizedBox(height: 18),

            // Term GPA Progression Bar Chart
            const AppCard(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '📈 Academic Growth Trend',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        '+0.15 GPA Growth',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF047857)),
                      ),
                    ],
                  ),
                  SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      _TrendBar(term: '1st Term', gpa: 3.75, height: 80, isSelected: false),
                      _TrendBar(term: '2nd Term', gpa: 3.90, height: 100, isSelected: true),
                      _TrendBar(term: 'Final (Target)', gpa: 3.95, height: 110, isSelected: false, isTarget: true),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Action button to View Official Marksheet
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton.icon(
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const ReportCardScreen()),
                ),
                icon: const Icon(Icons.workspace_premium, color: Colors.white, size: 20),
                label: const Text('View Official Terminal Marksheet (PDF)', style: TextStyle(fontWeight: FontWeight.w800)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1E3A8A),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 2,
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Subject Breakdown Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const SectionHeader(title: '📊 Subject Breakdown'),
                Text(
                  'Tap subject for test details',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade600),
                ),
              ],
            ),
            const SizedBox(height: 12),
            ...subjects.map((sub) => _DetailedSubjectCard(
                  subject: sub,
                  onTap: () => _showSubjectDetailSheet(context, sub),
                )),
          ],
        ),
      ),
    );
  }

  void _showCdcGradingGuide(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.school, color: AppTheme.primary, size: 24),
                SizedBox(width: 10),
                Text('CDC Nepal Grading Scale', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
              ],
            ),
            const SizedBox(height: 6),
            const Text(
              'National Standard mandated by the Ministry of Education & CDC Nepal:',
              style: TextStyle(fontSize: 11, color: AppTheme.grey),
            ),
            const SizedBox(height: 16),
            Table(
              border: TableBorder.all(color: const Color(0xFFCBD5E1)),
              children: const [
                TableRow(
                  decoration: BoxDecoration(color: Color(0xFF1E3A8A)),
                  children: [
                    _GuideHeaderCell(text: 'Score Interval'),
                    _GuideHeaderCell(text: 'Letter Grade'),
                    _GuideHeaderCell(text: 'Grade Point'),
                    _GuideHeaderCell(text: 'Descriptor'),
                  ],
                ),
                TableRow(children: [_GuideCell(text: '90 - 100%'), _GuideCell(text: 'A+', isBold: true), _GuideCell(text: '4.0'), _GuideCell(text: 'Outstanding')]),
                TableRow(children: [_GuideCell(text: '80 - 89%'), _GuideCell(text: 'A', isBold: true), _GuideCell(text: '3.6'), _GuideCell(text: 'Excellent')]),
                TableRow(children: [_GuideCell(text: '70 - 79%'), _GuideCell(text: 'B+', isBold: true), _GuideCell(text: '3.2'), _GuideCell(text: 'Very Good')]),
                TableRow(children: [_GuideCell(text: '60 - 69%'), _GuideCell(text: 'B', isBold: true), _GuideCell(text: '2.8'), _GuideCell(text: 'Good')]),
                TableRow(children: [_GuideCell(text: '50 - 59%'), _GuideCell(text: 'C+', isBold: true), _GuideCell(text: '2.4'), _GuideCell(text: 'Satisfactory')]),
                TableRow(children: [_GuideCell(text: '40 - 49%'), _GuideCell(text: 'C', isBold: true), _GuideCell(text: '2.0'), _GuideCell(text: 'Acceptable')]),
                TableRow(children: [_GuideCell(text: '35 - 39%'), _GuideCell(text: 'D', isBold: true), _GuideCell(text: '1.6'), _GuideCell(text: 'Basic')]),
                TableRow(children: [_GuideCell(text: 'Below 35%'), _GuideCell(text: 'NG', isBold: true, color: Color(0xFFDC2626)), _GuideCell(text: '0.0'), _GuideCell(text: 'Non-Graded')]),
              ],
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Got It'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showSubjectDetailSheet(BuildContext context, SubjectGrade sub) {
    final hasPr = sub.hasPractical;
    final thScore = hasPr ? (sub.thMarks?.toInt() ?? (sub.score * 0.75).round()) : sub.score.toInt();
    final prScore = hasPr ? (sub.prMarks?.toInt() ?? (sub.score * 0.25).round()) : 0;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: Color(sub.colorHex).withValues(alpha: 0.14),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(subjectIcon(sub.iconCode), color: Color(sub.colorHex), size: 26),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(sub.name, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
                      Text('${sub.code} • Educator: ${sub.teacher}', style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                    ],
                  ),
                ),
                GradeBadge(grade: sub.grade),
              ],
            ),
            const SizedBox(height: 20),

            // Continuous Assessment Breakdown
            const Text(
              'Continuous Assessment Breakdown:',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  _BreakdownRow(
                    title: 'Theory Terminal Exam (TH)',
                    score: '$thScore / ${hasPr ? 75 : 100}',
                    pct: thScore / (hasPr ? 75 : 100),
                    color: const Color(0xFF1E3A8A),
                  ),
                  if (hasPr) ...[
                    const Divider(height: 16),
                    _BreakdownRow(
                      title: 'Practical & Project Work (PR)',
                      score: '$prScore / 25',
                      pct: prScore / 25,
                      color: const Color(0xFF047857),
                    ),
                  ],
                  const Divider(height: 16),
                  const _BreakdownRow(
                    title: 'Class Participation & Attendance',
                    score: '5 / 5',
                    pct: 1.0,
                    color: Color(0xFFD97706),
                  ),
                  const Divider(height: 16),
                  const _BreakdownRow(
                    title: 'Periodic Homework & Assignments',
                    score: '9.5 / 10',
                    pct: 0.95,
                    color: Color(0xFF7C3AED),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Teacher Remarks & Recommendations
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Educator Notes (${sub.teacher}):',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF92400E)),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '"${sub.remarks}. Demonstrated high analytical clarity and completed experimental lab reports with perfection."',
                    style: const TextStyle(fontSize: 12, fontStyle: FontStyle.italic, color: Color(0xFF451A03), height: 1.3),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Close'),
              ),
            ),
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
        Text(value, style: const TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w900)),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.w600)),
      ],
    );
  }
}

class _TrendBar extends StatelessWidget {
  final String term;
  final double gpa;
  final double height;
  final bool isSelected;
  final bool isTarget;

  const _TrendBar({
    required this.term,
    required this.gpa,
    required this.height,
    required this.isSelected,
    this.isTarget = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          gpa.toStringAsFixed(2),
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w900,
            color: isSelected ? const Color(0xFF1E3A8A) : (isTarget ? const Color(0xFF047857) : const Color(0xFF64748B)),
          ),
        ),
        const SizedBox(height: 6),
        Container(
          width: 38,
          height: height,
          decoration: BoxDecoration(
            color: isSelected
                ? const Color(0xFF1E3A8A)
                : (isTarget ? const Color(0xFF10B981) : const Color(0xFFCBD5E1)),
            borderRadius: BorderRadius.circular(8),
            border: isTarget
                ? Border.all(color: const Color(0xFF047857), width: 1.5)
                : null,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          term,
          style: TextStyle(
            fontSize: 10,
            fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
            color: isSelected ? const Color(0xFF1E3A8A) : const Color(0xFF64748B),
          ),
        ),
      ],
    );
  }
}

class _DetailedSubjectCard extends StatelessWidget {
  final SubjectGrade subject;
  final VoidCallback onTap;

  const _DetailedSubjectCard({required this.subject, required this.onTap});

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
                    color: Color(subject.colorHex).withValues(alpha: 0.12),
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
                minHeight: 7,
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
                Row(
                  children: [
                    Text(
                      subject.remarks,
                      style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontStyle: FontStyle.italic),
                    ),
                    const SizedBox(width: 4),
                    const Icon(Icons.chevron_right, size: 14, color: AppTheme.grey),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _BreakdownRow extends StatelessWidget {
  final String title, score;
  final double pct;
  final Color color;

  const _BreakdownRow({
    required this.title,
    required this.score,
    required this.pct,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(title, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
            Text(score, style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w900, color: color)),
          ],
        ),
        const SizedBox(height: 4),
        ClipRRect(
          borderRadius: BorderRadius.circular(3),
          child: LinearProgressIndicator(
            value: pct.clamp(0.0, 1.0),
            backgroundColor: const Color(0xFFE2E8F0),
            valueColor: AlwaysStoppedAnimation<Color>(color),
            minHeight: 5,
          ),
        ),
      ],
    );
  }
}

class _GuideHeaderCell extends StatelessWidget {
  final String text;
  const _GuideHeaderCell({required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(6.0),
      child: Text(
        text,
        textAlign: TextAlign.center,
        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
      ),
    );
  }
}

class _GuideCell extends StatelessWidget {
  final String text;
  final bool isBold;
  final Color? color;

  const _GuideCell({required this.text, this.isBold = false, this.color});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(5.0),
      child: Text(
        text,
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 10,
          fontWeight: isBold ? FontWeight.w900 : FontWeight.w600,
          color: color ?? const Color(0xFF334155),
        ),
      ),
    );
  }
}
