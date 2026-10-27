import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';

class PayslipScreen extends StatefulWidget {
  const PayslipScreen({super.key});

  @override
  State<PayslipScreen> createState() => _PayslipScreenState();
}

class _PayslipScreenState extends State<PayslipScreen> {
  final List<Map<String, dynamic>> _payslips = [
    {
      'month': 'Ashwin 2081 (Sep/Oct 2024)',
      'status': 'Disbursed',
      'paymentDate': '२०८१ असोज ०२',
      'bank': 'Nabil Bank Ltd. (A/C: ****4092)',
      'basicSalary': 45000,
      'allowances': 15500, // Head + Transport
      'providentFund': 4500,
      'cit': 2000,
      'tax': 1500,
      'netSalary': 52500,
    },
    {
      'month': 'Bhadra 2081 (Aug/Sep 2024)',
      'status': 'Paid',
      'paymentDate': '२०८१ भदौ ०२',
      'bank': 'Nabil Bank Ltd. (A/C: ****4092)',
      'basicSalary': 45000,
      'allowances': 8500,
      'providentFund': 4500,
      'cit': 2000,
      'tax': 1200,
      'netSalary': 45800,
    },
    {
      'month': 'Shrawan 2081 (Jul/Aug 2024)',
      'status': 'Paid',
      'paymentDate': '२०८१ साउन ०२',
      'bank': 'Nabil Bank Ltd. (A/C: ****4092)',
      'basicSalary': 45000,
      'allowances': 8500,
      'providentFund': 4500,
      'cit': 2000,
      'tax': 1200,
      'netSalary': 45800,
    },
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final latest = _payslips.first;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Salary & Payslips (तलब विवरण)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Faculty Payroll & Tax Statements',
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
        padding: const EdgeInsets.fromLTRB(18, 6, 18, 90),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Latest Salary Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [
                    Color(0xFF0F4C81),
                    Color(0xFF1E3A8A),
                    Color(0xFF2563EB),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F4C81).withOpacity(0.4),
                    blurRadius: 18,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'LATEST SALARY (चालु महिना)',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text(
                          'CREDITED ✅',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 10,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'रु. ${latest['netSalary']}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 30,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${latest['month']} • ${latest['bank']}',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.8),
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Divider(color: Colors.white.withOpacity(0.2), height: 1),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _SalaryDetailMini(label: 'Basic Pay', value: 'रु. ${latest['basicSalary']}'),
                      _SalaryDetailMini(label: 'Allowances', value: 'रु. ${latest['allowances']}'),
                      _SalaryDetailMini(
                        label: 'CIT / PF / Tax',
                        value: '- रु. ${latest['providentFund'] + latest['cit'] + latest['tax']}',
                      ),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.08, end: 0),
            const SizedBox(height: 20),

            // Detailed Breakdown Card
            const SectionHeader(
              title: 'Earnings & Deductions (आय तथा कट्टी विवरण)',
              subtitle: 'Transparent government and school deductions',
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkCard : Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withOpacity(isDark ? 0.2 : 0.03),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Column(
                children: [
                  _BreakdownRow(title: 'Basic Monthly Salary (आधारभूत तलब)', amount: '+ रु. ${latest['basicSalary']}', isDeduction: false, isDark: isDark),
                  _BreakdownRow(title: 'Subject Head & Dearness Allowance', amount: '+ रु. ${latest['allowances']}', isDeduction: false, isDark: isDark),
                  Divider(height: 18, color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                  _BreakdownRow(title: 'Provident Fund (कर्मचारी सञ्चय कोष)', amount: '- रु. ${latest['providentFund']}', isDeduction: true, isDark: isDark),
                  _BreakdownRow(title: 'Citizen Investment Trust (नागरिक लगानी कोष)', amount: '- रु. ${latest['cit']}', isDeduction: true, isDark: isDark),
                  _BreakdownRow(title: 'Income Tax / Social Security (TDS)', amount: '- रु. ${latest['tax']}', isDeduction: true, isDark: isDark),
                  Divider(height: 20, color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Net Take Home Salary',
                        style: TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 14,
                          color: isDark ? Colors.white : AppTheme.dark,
                        ),
                      ),
                      Text(
                        'रु. ${latest['netSalary']}',
                        style: const TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 17,
                          color: AppTheme.green,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Past History
            const SectionHeader(
              title: 'Past Payslip History (अघिल्ला महिनाहरूको भौचर)',
              subtitle: 'Download signed PDF tax slips',
            ),
            const SizedBox(height: 10),
            ..._payslips.map((slip) => Container(
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkCard : Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                      width: 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.receipt_long_rounded, color: AppTheme.primary, size: 22),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              slip['month'] as String,
                              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Disbursed on ${slip['paymentDate']}',
                              style: const TextStyle(fontSize: 11, color: AppTheme.darkSubtext),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'रु. ${slip['netSalary']}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 13.5,
                            ),
                          ),
                          const SizedBox(height: 2),
                          GestureDetector(
                            onTap: () {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  backgroundColor: AppTheme.green,
                                  content: Text('Payslip PDF downloaded to device storage ✅'),
                                ),
                              );
                            },
                            child: const Text(
                              'PDF भौचर ⬇️',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.primary,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                )),
          ],
        ),
      ),
    );
  }
}

class _SalaryDetailMini extends StatelessWidget {
  final String label, value;
  const _SalaryDetailMini({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          value,
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 12),
        ),
        const SizedBox(height: 1),
        Text(
          label,
          style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 10, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}

class _BreakdownRow extends StatelessWidget {
  final String title, amount;
  final bool isDeduction;
  final bool isDark;

  const _BreakdownRow({
    required this.title,
    required this.amount,
    required this.isDeduction,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: isDark ? Colors.white70 : AppTheme.dark,
            ),
          ),
          Text(
            amount,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              color: isDeduction ? AppTheme.red : AppTheme.green,
            ),
          ),
        ],
      ),
    );
  }
}
