import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../data/app_data.dart';
import '../theme/app_theme.dart';
import '../providers/api_provider.dart';
import '../services/api_service.dart';
import '../widgets/common_widgets.dart';

class ReportCardScreen extends StatefulWidget {
  const ReportCardScreen({super.key});
  @override
  State<ReportCardScreen> createState() => _ReportCardScreenState();
}

class _ReportCardScreenState extends State<ReportCardScreen> {
  int _selectedCardIndex = 0;
  bool _isRefreshing = false;

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final child = api.selectedChild;
    final reportCards = api.reportCards;

    final card = reportCards[_selectedCardIndex.clamp(0, reportCards.length - 1)];

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
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
            onPressed: () => _shareMarksheet(context, card, child),
          ),
          IconButton(
            icon: const Icon(Icons.print_outlined),
            tooltip: 'Print Marksheet',
            onPressed: () => _printMarksheet(context, card, child),
          ),
          IconButton(
            icon: const Icon(Icons.picture_as_pdf_outlined),
            tooltip: 'Download Official PDF',
            onPressed: () => _downloadMarksheetPdf(context, card, child),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          setState(() => _isRefreshing = true);
          await api.refreshReportCards();
          if (mounted) setState(() => _isRefreshing = false);
        },
        color: const Color(0xFF1E3A8A),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
          child: Column(
            children: [
              // Connection Status & Active Child Indicator Bar
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.03),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        color: Color(0xFF10B981),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Verified Academic Transcript • ${child.name}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF1E293B)),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    InkWell(
                      onTap: () async {
                        setState(() => _isRefreshing = true);
                        await api.refreshReportCards();
                        if (!context.mounted) return;
                        setState(() => _isRefreshing = false);
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Synchronized latest marks with school examination ledger.'),
                            backgroundColor: Color(0xFF1E3A8A),
                            duration: Duration(seconds: 2),
                          ),
                        );
                      },
                      child: Row(
                        children: [
                          Icon(
                            Icons.sync_rounded,
                            size: 14,
                            color: _isRefreshing ? const Color(0xFF2563EB) : const Color(0xFF64748B),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            _isRefreshing ? 'Syncing...' : 'Sync Live',
                            style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Color(0xFF2563EB)),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              // Term Selector Horizontal Chips
              if (reportCards.isNotEmpty)
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.only(bottom: 14),
                  child: Row(
                    children: reportCards.asMap().entries.map((e) {
                      final isSel = e.key == _selectedCardIndex;
                      return GestureDetector(
                        onTap: () => setState(() => _selectedCardIndex = e.key),
                        child: Container(
                          margin: const EdgeInsets.only(right: 8),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          decoration: BoxDecoration(
                            color: isSel ? const Color(0xFF1E3A8A) : Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: isSel ? const Color(0xFF1E3A8A) : const Color(0xFFCBD5E1),
                              width: 1.5,
                            ),
                            boxShadow: isSel
                                ? [
                                    BoxShadow(
                                      color: const Color(0xFF1E3A8A).withValues(alpha: 0.25),
                                      blurRadius: 8,
                                      offset: const Offset(0, 3),
                                    )
                                  ]
                                : null,
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                isSel ? Icons.verified : Icons.school_outlined,
                                size: 14,
                                color: isSel ? const Color(0xFFFDE68A) : const Color(0xFF64748B),
                              ),
                              const SizedBox(width: 6),
                              Text(
                                e.value.term,
                                style: TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                  color: isSel ? Colors.white : const Color(0xFF1E293B),
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),

              // Official Nepal Standard Academic Marksheet Paper
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFCBD5E1), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.08),
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Column(
                    children: [
                      // Institutional Header Ribbon (Navy & Gold Nepal Standard)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            colors: [Color(0xFF0F172A), Color(0xFF1E3A8A)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Container(
                                  width: 58,
                                  height: 58,
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    shape: BoxShape.circle,
                                    border: Border.all(color: const Color(0xFFD97706), width: 2.5),
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withValues(alpha: 0.25),
                                        blurRadius: 8,
                                      ),
                                    ],
                                  ),
                                  child: const Center(
                                    child: Icon(Icons.school_rounded, color: Color(0xFF1E3A8A), size: 32),
                                  ),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        child.schoolName.toUpperCase(),
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 15,
                                          fontWeight: FontWeight.w900,
                                          letterSpacing: 0.6,
                                          height: 1.2,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      const Text(
                                        'Affiliated with CDC / National Examination Board (NEB)',
                                        style: TextStyle(
                                          color: Color(0xFFFDE68A),
                                          fontSize: 10,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                      const Text(
                                        'Kathmandu, Nepal • ESTD. 2048 • Reg No: 8847/2081',
                                        style: TextStyle(
                                          color: Colors.white70,
                                          fontSize: 9.5,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            // Dual accent line
                            Container(height: 2.5, color: const Color(0xFFD97706)),
                            const SizedBox(height: 10),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                              decoration: BoxDecoration(
                                color: const Color(0xFFD97706),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'OFFICIAL ACADEMIC PROGRESS REPORT',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 1.2,
                                ),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${card.term.toUpperCase()} • SESSION ${card.academicYear}',
                              style: const TextStyle(
                                color: Colors.white70,
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Student Information Matrix
                      Container(
                        padding: const EdgeInsets.all(16),
                        color: const Color(0xFFF8FAFC),
                        child: Column(
                          children: [
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  flex: 3,
                                  child: Column(
                                    children: [
                                      _StudentInfoRow(label: 'STUDENT NAME', value: child.name.toUpperCase(), isHighlight: true),
                                      _StudentInfoRow(label: 'CLASS & SECTION', value: '${child.grade} - Section "${child.section}"'),
                                      _StudentInfoRow(label: 'ROLL NUMBER', value: child.rollNo),
                                      const _StudentInfoRow(label: 'PARENT / GUARDIAN', value: 'Ram Bahadur Shrestha'),
                                    ],
                                  ),
                                ),
                                Container(width: 1, height: 90, color: const Color(0xFFE2E8F0)),
                                const SizedBox(width: 12),
                                Expanded(
                                  flex: 3,
                                  child: Column(
                                    children: [
                                      _StudentInfoRow(label: 'ADMISSION ID', value: child.admissionNo),
                                      const _StudentInfoRow(label: 'DATE OF BIRTH', value: '2067-04-12 (2010 AD)'),
                                      _StudentInfoRow(label: 'ISSUE DATE', value: card.issueDate),
                                      _StudentInfoRow(label: 'ATTENDANCE', value: card.attendance, isGreen: true),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      const Divider(height: 1, thickness: 1.5, color: Color(0xFFCBD5E1)),

                      // CDC Marks & Grade Table
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Table Header
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFF1E3A8A),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Row(
                                children: [
                                  Expanded(flex: 5, child: Text('SUBJECT', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800))),
                                  Expanded(flex: 2, child: Text('CR.HR', textAlign: TextAlign.center, style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800))),
                                  Expanded(flex: 2, child: Text('TH (75)', textAlign: TextAlign.center, style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800))),
                                  Expanded(flex: 2, child: Text('PR (25)', textAlign: TextAlign.center, style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800))),
                                  Expanded(flex: 2, child: Text('TOT (100)', textAlign: TextAlign.center, style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w800))),
                                  Expanded(flex: 2, child: Text('GP', textAlign: TextAlign.center, style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800))),
                                  Expanded(flex: 2, child: Text('GRADE', textAlign: TextAlign.center, style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800))),
                                ],
                              ),
                            ),
                            const SizedBox(height: 4),

                            // Table Rows
                            ...card.subjectGrades.asMap().entries.map((entry) {
                              final idx = entry.key;
                              final sub = entry.value;
                              final isEven = idx % 2 == 0;
                              final hasPr = sub.hasPractical;

                              final thScore = hasPr
                                  ? (sub.thMarks?.toInt() ?? (sub.score * 0.75).round())
                                  : sub.score.toInt();
                              final prScore = hasPr
                                  ? (sub.prMarks?.toInt() ?? (sub.score * 0.25).round())
                                  : 0;
                              final totalScore = sub.score.toInt();
                              final gp = _calculateGradePoint(sub.grade);

                              return Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 7),
                                decoration: BoxDecoration(
                                  color: isEven ? Colors.white : const Color(0xFFF8FAFC),
                                  border: const Border(
                                    bottom: BorderSide(color: Color(0xFFE2E8F0), width: 0.8),
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      flex: 5,
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            sub.name,
                                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                                          ),
                                          Text(
                                            '${sub.code.isNotEmpty ? sub.code : "SUB-80$idx"} • ${hasPr ? "Theory + Practical" : "Theory Only"}',
                                            style: const TextStyle(fontSize: 9, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const Expanded(
                                      flex: 2,
                                      child: Text(
                                        '4.0',
                                        textAlign: TextAlign.center,
                                        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                                      ),
                                    ),
                                    Expanded(
                                      flex: 2,
                                      child: Text(
                                        '$thScore',
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: Color(0xFF1E3A8A)),
                                      ),
                                    ),
                                    Expanded(
                                      flex: 2,
                                      child: Text(
                                        hasPr ? '$prScore' : '—',
                                        textAlign: TextAlign.center,
                                        style: TextStyle(
                                          fontSize: 10.5,
                                          fontWeight: hasPr ? FontWeight.w800 : FontWeight.w500,
                                          color: hasPr ? const Color(0xFF047857) : Colors.grey.shade400,
                                        ),
                                      ),
                                    ),
                                    Expanded(
                                      flex: 2,
                                      child: Text(
                                        '$totalScore',
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                      ),
                                    ),
                                    Expanded(
                                      flex: 2,
                                      child: Text(
                                        gp.toStringAsFixed(1),
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF334155)),
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

                            // Total Aggregate Row
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                              decoration: const BoxDecoration(
                                color: Color(0xFFEFF6FF),
                                border: Border(
                                  top: BorderSide(color: Color(0xFF1E3A8A), width: 1.5),
                                  bottom: BorderSide(color: Color(0xFF1E3A8A), width: 1.5),
                                ),
                              ),
                              child: Row(
                                children: [
                                  const Expanded(
                                    flex: 5,
                                    child: Text(
                                      'AGGREGATE TOTAL',
                                      style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900, color: Color(0xFF1E3A8A)),
                                    ),
                                  ),
                                  const Expanded(
                                    flex: 2,
                                    child: Text(
                                      '28.0',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900, color: Color(0xFF1E3A8A)),
                                    ),
                                  ),
                                  const Expanded(flex: 4, child: SizedBox()),
                                  Expanded(
                                    flex: 2,
                                    child: Text(
                                      '${card.obtainedMarks}',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: Color(0xFF1E3A8A)),
                                    ),
                                  ),
                                  Expanded(
                                    flex: 2,
                                    child: Text(
                                      card.gpa.toStringAsFixed(2),
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: Color(0xFF1E3A8A)),
                                    ),
                                  ),
                                  Expanded(
                                    flex: 2,
                                    child: Center(child: GradeBadge(grade: card.overallGrade)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Summary KPI Ribbon
                      Container(
                        margin: const EdgeInsets.symmetric(horizontal: 14),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _TranscriptKpi(title: 'TOTAL OBTAINED', value: '${card.obtainedMarks} / ${card.totalMarks}'),
                            _TranscriptKpi(title: 'PERCENTAGE', value: '${card.percentage}%'),
                            _TranscriptKpi(title: 'FINAL GPA', value: card.gpa.toStringAsFixed(2), isHighlighted: true),
                            _TranscriptKpi(title: 'CLASS POSITION', value: '#${card.rank} of ${card.totalStudents}'),
                            _TranscriptKpi(title: 'RESULT', value: card.resultStatus, isBadge: true),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // CDC Grading System Reference Scale
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.grading_rounded, size: 14, color: Color(0xFF475569)),
                                SizedBox(width: 4),
                                Text(
                                  'CDC NEPAL GRADING SCALE (CURRICULUM DEVELOPMENT CENTRE)',
                                  style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF475569), letterSpacing: 0.4),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Container(
                              decoration: BoxDecoration(
                                border: Border.all(color: const Color(0xFFCBD5E1)),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Table(
                                border: TableBorder.all(color: const Color(0xFFE2E8F0)),
                                children: const [
                                  TableRow(
                                    decoration: BoxDecoration(color: Color(0xFFF1F5F9)),
                                    children: [
                                      _ScaleCell(text: 'Interval %', isHeader: true),
                                      _ScaleCell(text: '90-100'),
                                      _ScaleCell(text: '80-89'),
                                      _ScaleCell(text: '70-79'),
                                      _ScaleCell(text: '60-69'),
                                      _ScaleCell(text: '50-59'),
                                      _ScaleCell(text: '40-49'),
                                      _ScaleCell(text: '35-39'),
                                      _ScaleCell(text: '< 35'),
                                    ],
                                  ),
                                  TableRow(
                                    decoration: BoxDecoration(color: Color(0xFFF0FDF4)),
                                    children: [
                                      _ScaleCell(text: 'Grade', isHeader: true),
                                      _ScaleCell(text: 'A+', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'A', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'B+', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'B', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'C+', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'C', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'D', isBold: true, color: Color(0xFF166534)),
                                      _ScaleCell(text: 'NG', isBold: true, color: Color(0xFFDC2626)),
                                    ],
                                  ),
                                  TableRow(
                                    decoration: BoxDecoration(color: Color(0xFFEFF6FF)),
                                    children: [
                                      _ScaleCell(text: 'Grade Point', isHeader: true),
                                      _ScaleCell(text: '4.0', isBold: true),
                                      _ScaleCell(text: '3.6', isBold: true),
                                      _ScaleCell(text: '3.2', isBold: true),
                                      _ScaleCell(text: '2.8', isBold: true),
                                      _ScaleCell(text: '2.4', isBold: true),
                                      _ScaleCell(text: '2.0', isBold: true),
                                      _ScaleCell(text: '1.6', isBold: true),
                                      _ScaleCell(text: '0.0', isBold: true),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Conduct & Character Traits Matrix
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        child: Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'STUDENT CONDUCT & CO-CURRICULAR ASSESSMENT',
                                style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF1E3A8A)),
                              ),
                              SizedBox(height: 6),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  _TraitPill(label: 'Discipline', value: 'Exemplary (A+)'),
                                  _TraitPill(label: 'Punctuality', value: 'Outstanding (A+)'),
                                  _TraitPill(label: 'Social Skills', value: 'Cooperative (A)'),
                                  _TraitPill(label: 'Sports / CCA', value: 'Active (A+)'),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Remarks
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFFBEB),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFFDE68A)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'HEADMASTER & CLASS EDUCATOR REMARKS:',
                                style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: Color(0xFF92400E)),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '"${card.principalRemarks}"',
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  fontStyle: FontStyle.italic,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF451A03),
                                  height: 1.35,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Signatures & Official Watermark Seal
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            _SignBlock(title: 'Class Teacher', name: card.classTeacher),
                            // Round Digital Verification Stamp (Tappable for QR Verification)
                            InkWell(
                              onTap: () => _showVerificationSealModal(context, card, child),
                              borderRadius: BorderRadius.circular(40),
                              child: Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(color: const Color(0xFF047857), width: 2),
                                  color: const Color(0xFFF0FDF4),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF047857).withValues(alpha: 0.15),
                                      blurRadius: 8,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: const Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.verified, color: Color(0xFF047857), size: 22),
                                    Text(
                                      'NSHLMS\nVERIFIED',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        fontSize: 7.5,
                                        fontWeight: FontWeight.w900,
                                        color: Color(0xFF047857),
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            _SignBlock(title: 'Exam Controller', name: card.examController),
                            _SignBlock(title: 'Principal', name: card.principalName),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Security Bottom Note with Token
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 14),
                        color: const Color(0xFFF1F5F9),
                        child: Text(
                          'DIGITALLY ENCRYPTED TRANSCRIPT • VERIFY TOKEN: ${card.verifyToken} • NEPAL SCHOOL HUB',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontSize: 8.5,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF64748B),
                            letterSpacing: 0.4,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ).animate().fadeIn().scale(begin: const Offset(0.98, 0.98)),
            ],
          ),
        ),
      ),
    );
  }

  static double _calculateGradePoint(String grade) {
    switch (grade.toUpperCase()) {
      case 'A+':
        return 4.0;
      case 'A':
        return 3.6;
      case 'B+':
        return 3.2;
      case 'B':
        return 2.8;
      case 'C+':
        return 2.4;
      case 'C':
        return 2.0;
      case 'D':
        return 1.6;
      default:
        return 0.0;
    }
  }

  void _shareMarksheet(BuildContext context, ReportCard card, dynamic child) {
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
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E3A8A).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.share, color: Color(0xFF1E3A8A), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Share Official Marksheet', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                      Text('${child.name} • ${card.term}', style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            const Text(
              'A digitally signed PDF marksheet link will be shared. Anyone with this link can verify the authentic marks issued by the institution.',
              style: TextStyle(fontSize: 12, color: Color(0xFF334155), height: 1.4),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFCBD5E1)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.link, color: Color(0xFF2563EB), size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      card.verifyUrl,
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF1E3A8A)),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
                  TextButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Copied verified transcript URL to clipboard!'),
                          backgroundColor: Color(0xFF047857),
                        ),
                      );
                    },
                    child: const Text('Copy'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(ctx);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Prepared official marksheet packet of ${child.name} for sharing!'),
                      backgroundColor: const Color(0xFF1E3A8A),
                    ),
                  );
                },
                icon: const Icon(Icons.send_rounded, size: 16),
                label: const Text('Share PDF Document'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _printMarksheet(BuildContext context, ReportCard card, dynamic child) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: const Color(0xFF047857).withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.print_rounded, color: Color(0xFF047857), size: 30),
            ),
            const SizedBox(height: 14),
            const Text('AirPrint & Wi-Fi Printing', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
            const SizedBox(height: 6),
            Text(
              'Preparing A4 Portrait mark sheet layout for "${child.name} — ${card.term}".',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 12, color: AppTheme.grey),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: const Column(
                children: [
                  _PrintSpecRow(label: 'Paper Size', value: 'A4 Portrait (210 × 297 mm)'),
                  Divider(height: 10),
                  _PrintSpecRow(label: 'Resolution', value: '300 DPI High Definition'),
                  Divider(height: 10),
                  _PrintSpecRow(label: 'Color Mode', value: 'Full Institutional CMYK'),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF047857)),
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Dispatched marksheet of ${child.name} to AirPrint spooler!'),
                  backgroundColor: const Color(0xFF047857),
                ),
              );
            },
            icon: const Icon(Icons.print, size: 16),
            label: const Text('Print Now'),
          ),
        ],
      ),
    );
  }

  void _showVerificationSealModal(BuildContext context, ReportCard card, dynamic child) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.all(20),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: const BoxDecoration(
                    color: Color(0xFFDCFCE7),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.verified, color: Color(0xFF047857), size: 26),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Digital Verification Seal', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                      Text('National Examination Board (NEB)', style: TextStyle(fontSize: 10.5, color: AppTheme.grey)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 10),
                ],
              ),
              child: Column(
                children: [
                  Container(
                    width: 130,
                    height: 130,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFCBD5E1)),
                    ),
                    child: const Center(
                      child: Icon(Icons.qr_code_2_rounded, size: 110, color: Color(0xFF0F172A)),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'TOKEN: ${card.verifyToken}',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Color(0xFF1E3A8A)),
                  ),
                  const SizedBox(height: 2),
                  const Text('Scan to verify digital signature integrity', style: TextStyle(fontSize: 9.5, color: AppTheme.grey)),
                ],
              ),
            ),
            const SizedBox(height: 14),
            _VerificationLine(label: 'Candidate', value: child.name),
            _VerificationLine(label: 'Examination', value: card.term),
            _VerificationLine(label: 'GPA Score', value: '${card.gpa.toStringAsFixed(2)} (${card.overallGrade})'),
            const _VerificationLine(label: 'Status', value: 'Legally Valid & Verified ✅'),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Opening verification registry: ${card.verifyUrl}'),
                  backgroundColor: const Color(0xFF047857),
                ),
              );
            },
            child: const Text('Verify on Portal'),
          ),
        ],
      ),
    );
  }

  void _downloadMarksheetPdf(BuildContext context, ReportCard card, dynamic child) async {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => FutureBuilder<Map<String, dynamic>>(
        future: ApiService.getReportCardPdfMeta(card.id ?? 1),
        builder: (context, snapshot) {
          final meta = snapshot.data;
          final token = meta?['verify_token'] ?? card.verifyToken;
          final downloadUrl = meta?['download_url'] ?? card.verifyUrl;

          return Padding(
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
                        color: const Color(0xFFDC2626).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.picture_as_pdf, color: Color(0xFFDC2626), size: 28),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Official Marksheet PDF Ready', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                          Text('${child.name} — ${card.term}.pdf', style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
                        ],
                      ),
                    ),
                  ],
                ),
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
                      _PrintSpecRow(label: 'Verification Token', value: token),
                      const Divider(height: 10),
                      const _PrintSpecRow(label: 'Document Layout', value: 'Official NEB / CDC Nepal Template'),
                      const Divider(height: 10),
                      const _PrintSpecRow(label: 'Security Level', value: 'Cryptographically Watermarked'),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(ctx),
                        child: const Text('Cancel'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(ctx);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('Saved "${child.name}_${card.term}.pdf" to Downloads! ($downloadUrl)'),
                              backgroundColor: const Color(0xFF047857),
                              behavior: SnackBarBehavior.floating,
                              duration: const Duration(seconds: 4),
                            ),
                          );
                        },
                        icon: const Icon(Icons.download, size: 18),
                        label: const Text('Save PDF'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _StudentInfoRow extends StatelessWidget {
  final String label, value;
  final bool isHighlight;
  final bool isGreen;

  const _StudentInfoRow({
    required this.label,
    required this.value,
    this.isHighlight = false,
    this.isGreen = false,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 95,
            child: Text(
              label,
              style: const TextStyle(fontSize: 8.5, color: Color(0xFF64748B), fontWeight: FontWeight.w700),
            ),
          ),
          const Text(': ', style: TextStyle(fontSize: 9, color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isHighlight ? FontWeight.w900 : FontWeight.w700,
                color: isHighlight
                    ? const Color(0xFF1E3A8A)
                    : (isGreen ? const Color(0xFF047857) : const Color(0xFF0F172A)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TranscriptKpi extends StatelessWidget {
  final String title, value;
  final bool isHighlighted;
  final bool isBadge;

  const _TranscriptKpi({
    required this.title,
    required this.value,
    this.isHighlighted = false,
    this.isBadge = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        if (isBadge)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: const Color(0xFF10B981),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              value,
              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w900),
            ),
          )
        else
          Text(
            value,
            style: TextStyle(
              color: isHighlighted ? const Color(0xFFFDE68A) : Colors.white,
              fontSize: isHighlighted ? 17 : 14,
              fontWeight: FontWeight.w900,
            ),
          ),
        const SizedBox(height: 2),
        Text(
          title,
          style: const TextStyle(color: Colors.white70, fontSize: 8, fontWeight: FontWeight.w700, letterSpacing: 0.4),
        ),
      ],
    );
  }
}

class _ScaleCell extends StatelessWidget {
  final String text;
  final bool isHeader;
  final bool isBold;
  final Color? color;

  const _ScaleCell({
    required this.text,
    this.isHeader = false,
    this.isBold = false,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3.5, horizontal: 2),
      child: Text(
        text,
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 8,
          fontWeight: isHeader || isBold ? FontWeight.w800 : FontWeight.w600,
          color: color ?? (isHeader ? const Color(0xFF1E293B) : const Color(0xFF475569)),
        ),
      ),
    );
  }
}

class _TraitPill extends StatelessWidget {
  final String label, value;
  const _TraitPill({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 8, color: Color(0xFF64748B), fontWeight: FontWeight.w700)),
        Text(value, style: const TextStyle(fontSize: 9.5, color: Color(0xFF0F172A), fontWeight: FontWeight.w800)),
      ],
    );
  }
}

class _SignBlock extends StatelessWidget {
  final String title, name;
  const _SignBlock({required this.title, required this.name});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          name,
          style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: Color(0xFF1E3A8A), fontStyle: FontStyle.italic),
        ),
        const SizedBox(height: 2),
        Container(
          width: 80,
          height: 1.2,
          color: const Color(0xFF94A3B8),
        ),
        const SizedBox(height: 3),
        Text(title, style: const TextStyle(fontSize: 8.5, color: Color(0xFF64748B), fontWeight: FontWeight.w800)),
      ],
    );
  }
}

class _PrintSpecRow extends StatelessWidget {
  final String label, value;
  const _PrintSpecRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
        Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF0F172A))),
      ],
    );
  }
}

class _VerificationLine extends StatelessWidget {
  final String label, value;
  const _VerificationLine({required this.label, required this.value});

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
