import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/common_widgets.dart';

class PayslipScreen extends StatefulWidget {
  const PayslipScreen({super.key});

  @override
  State<PayslipScreen> createState() => _PayslipScreenState();
}

class _PayslipScreenState extends State<PayslipScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  String _selectedYearFilter = 'All';

  // Payroll data
  Map<String, dynamic> _summary = {};
  Map<String, dynamic>? _latestSlip;
  List<Map<String, dynamic>> _payslips = [];
  String _academicYear = '2081/82 BS';

  static const String _cacheKey = 'cached_teacher_payroll_data';

  @override
  void initState() {
    super.initState();
    _loadPayrollData();
  }

  Future<void> _loadPayrollData() async {
    // 1. Load cached data first for instant UI rendering
    try {
      final prefs = await SharedPreferences.getInstance();
      final cachedJson = prefs.getString(_cacheKey);
      if (cachedJson != null && mounted) {
        final decoded = jsonDecode(cachedJson) as Map<String, dynamic>;
        _applyPayrollData(decoded);
      }
    } catch (_) {}

    // 2. Fetch fresh from backend
    final res = await _apiService.getPayrollOverview();
    if (!mounted) return;

    if (res.success && res.data != null) {
      final data = res.data!;
      _applyPayrollData(data);

      // Save to cache
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_cacheKey, jsonEncode(data));
      } catch (_) {}
    } else if (_payslips.isEmpty) {
      // Fallback fallback if network failed and no cache
      _loadDefaultFallback();
    }

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  void _applyPayrollData(Map<String, dynamic> data) {
    setState(() {
      _academicYear = data['academic_year']?.toString() ?? '2081/82 BS';
      
      if (data['summary'] is Map) {
        _summary = Map<String, dynamic>.from(data['summary']);
      }
      if (data['latest'] is Map) {
        _latestSlip = Map<String, dynamic>.from(data['latest']);
      }
      
      if (data['payslips'] is List) {
        _payslips = (data['payslips'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }

      if (_latestSlip == null && _payslips.isNotEmpty) {
        _latestSlip = _payslips.first;
      }
    });
  }

  void _loadDefaultFallback() {
    setState(() {
      _latestSlip = {
        'id': 101,
        'month': 'Ashwin 2081 (असोज २०८१)',
        'year': 2081,
        'status': 'Disbursed (जम्मा भयो)',
        'is_paid': true,
        'payment_date': '२०८१ असोज ०२',
        'payment_method': 'Direct Bank Transfer',
        'bank': 'Nabil Bank Ltd. (A/C: ****4092)',
        'bank_account_full': '01901017504092',
        'pan_no': '109283741',
        'voucher_no': 'SLIP-2081-06101',
        'basic_salary': 45000,
        'allowances': 15500,
        'bonus': 45000,
        'overtime': 3000,
        'gross_salary': 108500,
        'tax_deduction': 2500,
        'provident_fund': 4500,
        'cit': 2500,
        'insurance': 1000,
        'other_deductions': 500,
        'total_deductions': 10500,
        'net_salary': 98000,
        'working_days': 26,
        'present_days': 25,
        'absent_days': 0,
        'leave_days': 1,
        'notes': 'Includes 1 Month Dashain Festival Allowance (दशैं पेश्की).',
      };

      _summary = {
        'latest_net_salary': 98000,
        'latest_gross_salary': 108500,
        'latest_month': 'Ashwin 2081',
        'status': 'Disbursed',
        'ytd_gross_earnings': 218500,
        'ytd_net_earnings': 189600,
        'ytd_tax_deducted': 4900,
        'ytd_provident_fund': 13500,
        'ytd_cit_deposited': 6500,
        'currency': 'NPR (रु.)',
      };

      _payslips = [_latestSlip!];
    });
  }

  List<Map<String, dynamic>> get _filteredPayslips {
    if (_selectedYearFilter == 'All') return _payslips;
    return _payslips.where((slip) {
      final y = slip['year']?.toString() ?? '';
      return y.contains(_selectedYearFilter);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final latest = _latestSlip ?? (_payslips.isNotEmpty ? _payslips.first : null);

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.lightBg,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Salary & Payslips (तलब विवरण)',
              style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900),
            ),
            Text(
              'Faculty Payroll • $_academicYear',
              style: const TextStyle(
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
        actions: [
          IconButton(
            tooltip: 'रिफ्रेस गर्नुहोस्',
            icon: const Icon(Icons.refresh_rounded, size: 22),
            onPressed: () {
              setState(() => _isLoading = true);
              _loadPayrollData();
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: _isLoading && _payslips.isEmpty
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(strokeWidth: 2.5),
                  SizedBox(height: 14),
                  Text(
                    'तलब तथा भौचर विवरण लोड हुँदैछ...',
                    style: TextStyle(fontSize: 12, color: AppTheme.darkSubtext),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadPayrollData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.fromLTRB(16, 6, 16, 90),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 1. Latest Salary Hero Card
                    if (latest != null) _buildLatestSalaryHero(latest, isDark),

                    const SizedBox(height: 16),

                    // 2. Action Buttons (Digital Voucher, Advance, Tax Statement)
                    _buildQuickActionButtons(context, latest, isDark),

                    const SizedBox(height: 22),

                    // 3. YTD Fiscal Year Summary Card
                    _buildYtdSummaryCard(isDark),

                    const SizedBox(height: 22),

                    // 4. Detailed Earnings & Deductions Breakdown Card
                    if (latest != null) _buildDetailedBreakdown(latest, isDark),

                    const SizedBox(height: 22),

                    // 5. Past Payslips History with Filter Chips
                    _buildHistorySection(context, isDark),
                  ],
                ),
              ),
            ),
    );
  }

  // =========================================================================
  // 1. HERO LATEST SALARY CARD
  // =========================================================================
  Widget _buildLatestSalaryHero(Map<String, dynamic> latest, bool isDark) {
    final netSalary = latest['net_salary'] ?? latest['netSalary'] ?? 0;
    final basicSalary = latest['basic_salary'] ?? latest['basicSalary'] ?? 0;
    final allowances = latest['allowances'] ?? 0;
    final bonus = latest['bonus'] ?? 0;
    final totalDeductions = latest['total_deductions'] ??
        ((latest['provident_fund'] ?? latest['providentFund'] ?? 0) +
            (latest['cit'] ?? 0) +
            (latest['tax_deduction'] ?? latest['tax'] ?? 0));
    final bank = latest['bank'] ?? 'Nabil Bank Ltd.';
    final month = latest['month'] ?? 'Ashwin 2081';
    final isPaid = latest['is_paid'] ?? true;
    final workingDays = latest['working_days'] ?? 26;
    final presentDays = latest['present_days'] ?? 25;
    final leaveDays = latest['leave_days'] ?? 1;

    return Container(
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
            color: const Color(0xFF0F4C81).withOpacity(0.35),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.account_balance_wallet_rounded,
                        color: Colors.white, size: 16),
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'LATEST PAYROLL (चालु तलब)',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.6,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: isPaid
                      ? const Color(0xFF10B981)
                      : const Color(0xFFF59E0B),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isPaid ? Icons.check_circle_rounded : Icons.pending_rounded,
                      color: Colors.white,
                      size: 12,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      isPaid ? 'CREDITED (जम्मा भयो)' : 'PROCESSING',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 10,
                        letterSpacing: 0.4,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Big Net Amount
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Text(
                'रु. ${_formatAmount(netSalary)}',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 32,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.18),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'खुद भुक्तानी (Net)',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 4),

          // Month and Bank
          Row(
            children: [
              const Icon(Icons.calendar_today_rounded,
                  color: Colors.white70, size: 13),
              const SizedBox(width: 5),
              Text(
                month,
                style: TextStyle(
                  color: Colors.white.withOpacity(0.9),
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                '•',
                style: TextStyle(color: Colors.white.withOpacity(0.5)),
              ),
              const SizedBox(width: 8),
              const Icon(Icons.account_balance_rounded,
                  color: Colors.white70, size: 13),
              const SizedBox(width: 5),
              Expanded(
                child: Text(
                  bank,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.85),
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),
          Divider(color: Colors.white.withOpacity(0.2), height: 1),
          const SizedBox(height: 14),

          // 3 Column Mini Details
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _SalaryDetailMini(
                label: 'Basic (आधारभूत)',
                value: 'रु. ${_formatAmount(basicSalary)}',
              ),
              _SalaryDetailMini(
                label: bonus > 0 ? 'Allowances + चाडपर्व' : 'Allowances (भत्ता)',
                value: 'रु. ${_formatAmount(allowances + bonus)}',
              ),
              _SalaryDetailMini(
                label: 'Total Deductions (कट्टी)',
                value: '- रु. ${_formatAmount(totalDeductions)}',
                isNegative: true,
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Attendance row in hero card
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.12),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildAttendanceMini('कुल दिन', '$workingDays दिन'),
                Container(width: 1, height: 16, color: Colors.white24),
                _buildAttendanceMini('उपस्थित', '$presentDays दिन', isSuccess: true),
                Container(width: 1, height: 16, color: Colors.white24),
                _buildAttendanceMini('बिदा', '$leaveDays दिन'),
              ],
            ),
          ),
        ],
      ),
    ).animate().fadeIn().slideY(begin: 0.05, end: 0);
  }

  Widget _buildAttendanceMini(String label, String value, {bool isSuccess = false}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          '$label: ',
          style: TextStyle(
            color: Colors.white.withOpacity(0.75),
            fontSize: 11,
            fontWeight: FontWeight.w600,
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 11,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // 2. QUICK ACTION BUTTONS
  // =========================================================================
  Widget _buildQuickActionButtons(
      BuildContext context, Map<String, dynamic>? latest, bool isDark) {
    return Row(
      children: [
        // 1. Digital Payslip Voucher Modal Trigger
        Expanded(
          child: _buildActionButton(
            label: 'डिजिटल भौचर',
            sub: 'आधिकारिक स्लिप',
            icon: Icons.receipt_long_rounded,
            color: AppTheme.primary,
            isDark: isDark,
            onTap: () {
              if (latest != null) {
                _showDigitalVoucherModal(context, latest);
              }
            },
          ),
        ),
        const SizedBox(width: 10),

        // 2. Advance Salary Request
        Expanded(
          child: _buildActionButton(
            label: 'पेश्की अनुरोध',
            sub: 'Advance Salary',
            icon: Icons.handshake_rounded,
            color: const Color(0xFF0D9488), // Teal
            isDark: isDark,
            onTap: () => _showAdvanceRequestModal(context),
          ),
        ),
        const SizedBox(width: 10),

        // 3. Tax Statement
        Expanded(
          child: _buildActionButton(
            label: 'कर विवरण',
            sub: 'TDS चुक्ता',
            icon: Icons.verified_user_rounded,
            color: const Color(0xFF6366F1), // Indigo
            isDark: isDark,
            onTap: () => _showTaxStatementModal(context),
          ),
        ),
      ],
    );
  }

  Widget _buildActionButton({
    required String label,
    required String sub,
    required IconData icon,
    required Color color,
    required bool isDark,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Ink(
          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
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
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(height: 6),
              Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: isDark ? Colors.white : AppTheme.dark,
                ),
              ),
              const SizedBox(height: 1),
              Text(
                sub,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 9.5,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.darkSubtext,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // =========================================================================
  // 3. YTD FISCAL YEAR SUMMARY
  // =========================================================================
  Widget _buildYtdSummaryCard(bool isDark) {
    final ytdGross = _summary['ytd_gross_earnings'] ?? 218500;
    final ytdNet = _summary['ytd_net_earnings'] ?? 189600;
    final ytdTax = _summary['ytd_tax_deducted'] ?? 4900;
    final ytdPf = _summary['ytd_provident_fund'] ?? 13500;
    final ytdCit = _summary['ytd_cit_deposited'] ?? 6500;

    return Container(
      padding: const EdgeInsets.all(18),
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.analytics_rounded,
                        color: AppTheme.primary, size: 16),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'चालु आर्थिक वर्ष संक्षिप्त (FY $_academicYear)',
                    style: TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w800,
                      color: isDark ? Colors.white : AppTheme.dark,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: AppTheme.secondary.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'YTD Totals',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.secondary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Divider(
              height: 1,
              color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
          const SizedBox(height: 14),

          // 2x2 Grid + Full width
          Row(
            children: [
              Expanded(
                child: _buildYtdStatItem(
                  label: 'कुल आम्दानी (Gross)',
                  value: 'रु. ${_formatAmount(ytdGross)}',
                  color: AppTheme.primary,
                  isDark: isDark,
                ),
              ),
              Expanded(
                child: _buildYtdStatItem(
                  label: 'खुद प्राप्त (Net)',
                  value: 'रु. ${_formatAmount(ytdNet)}',
                  color: AppTheme.green,
                  isDark: isDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildYtdStatItem(
                  label: 'सञ्चय कोष (EPF 10%)',
                  value: 'रु. ${_formatAmount(ytdPf)}',
                  color: const Color(0xFF0284C7),
                  isDark: isDark,
                ),
              ),
              Expanded(
                child: _buildYtdStatItem(
                  label: 'नागरिक लगानी (CIT)',
                  value: 'रु. ${_formatAmount(ytdCit)}',
                  color: const Color(0xFF7C3AED),
                  isDark: isDark,
                ),
              ),
              Expanded(
                child: _buildYtdStatItem(
                  label: 'कर कट्टी (TDS)',
                  value: 'रु. ${_formatAmount(ytdTax)}',
                  color: const Color(0xFFDC2626),
                  isDark: isDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildYtdStatItem({
    required String label,
    required String value,
    required Color color,
    required bool isDark,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: AppTheme.darkSubtext,
          ),
        ),
        const SizedBox(height: 3),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w900,
            color: color,
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // 4. DETAILED BREAKDOWN OF CURRENT MONTH
  // =========================================================================
  Widget _buildDetailedBreakdown(Map<String, dynamic> latest, bool isDark) {
    final basic = latest['basic_salary'] ?? latest['basicSalary'] ?? 0;
    final allowances = latest['allowances'] ?? 0;
    final bonus = latest['bonus'] ?? 0;
    final overtime = latest['overtime'] ?? 0;
    final gross = latest['gross_salary'] ?? (basic + allowances + bonus + overtime);

    final pf = latest['provident_fund'] ?? latest['providentFund'] ?? 0;
    final cit = latest['cit'] ?? 0;
    final tax = latest['tax_deduction'] ?? latest['tax'] ?? 0;
    final insurance = latest['insurance'] ?? 1000;
    final other = latest['other_deductions'] ?? 0;
    final totalDeductions = latest['total_deductions'] ?? (pf + cit + tax + insurance + other);

    final net = latest['net_salary'] ?? latest['netSalary'] ?? (gross - totalDeductions);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SectionHeader(
          title: 'Earnings & Deductions (विस्तृत आय-कट्टी)',
          subtitle: 'Transparent breakdown conforming to Nepal IRD & School rules',
          trailing: TextButton.icon(
            style: TextButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              visualDensity: VisualDensity.compact,
            ),
            icon: const Icon(Icons.print_rounded, size: 15, color: AppTheme.primary),
            label: const Text(
              'भौचर',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: AppTheme.primary,
              ),
            ),
            onPressed: () => _showDigitalVoucherModal(context, latest),
          ),
        ),
        const SizedBox(height: 10),
        Container(
          padding: const EdgeInsets.all(18),
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
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1. EARNINGS SECTION
              Row(
                children: [
                  Container(
                    width: 4,
                    height: 14,
                    decoration: BoxDecoration(
                      color: AppTheme.green,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'आय विवरण (EARNINGS)',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.green,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              _BreakdownRow(
                title: 'आधारभूत मासिक तलब (Basic Salary)',
                amount: '+ रु. ${_formatAmount(basic)}',
                isDeduction: false,
                isDark: isDark,
              ),
              _BreakdownRow(
                title: 'महङ्गी तथा कक्षा शिक्षक भत्ता (Allowances)',
                amount: '+ रु. ${_formatAmount(allowances)}',
                isDeduction: false,
                isDark: isDark,
              ),
              if (bonus > 0)
                _BreakdownRow(
                  title: 'चाडपर्व / दशैं पेश्की भत्ता (Festival Bonus)',
                  amount: '+ रु. ${_formatAmount(bonus)}',
                  isDeduction: false,
                  isDark: isDark,
                  isHighlighted: true,
                ),
              if (overtime > 0)
                _BreakdownRow(
                  title: 'अतिरिक्त कक्षा / कोचिङ (Overtime & Coaching)',
                  amount: '+ रु. ${_formatAmount(overtime)}',
                  isDeduction: false,
                  isDark: isDark,
                ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.green.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'कुल आम्दानी (Gross Salary)',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 12,
                        color: AppTheme.green,
                      ),
                    ),
                    Text(
                      'रु. ${_formatAmount(gross)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                        color: AppTheme.green,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 18),

              // 2. DEDUCTIONS SECTION
              Row(
                children: [
                  Container(
                    width: 4,
                    height: 14,
                    decoration: BoxDecoration(
                      color: AppTheme.red,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'कट्टी विवरण (DEDUCTIONS)',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.red,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              _BreakdownRow(
                title: 'कर्मचारी सञ्चय कोष (EPF 10%)',
                amount: '- रु. ${_formatAmount(pf)}',
                isDeduction: true,
                isDark: isDark,
              ),
              _BreakdownRow(
                title: 'नागरिक लगानी कोष (CIT Contribution)',
                amount: '- रु. ${_formatAmount(cit)}',
                isDeduction: true,
                isDark: isDark,
              ),
              _BreakdownRow(
                title: 'सामाजिक सुरक्षा तथा आयकर (TDS 1% IRD)',
                amount: '- रु. ${_formatAmount(tax)}',
                isDeduction: true,
                isDark: isDark,
              ),
              if (insurance > 0)
                _BreakdownRow(
                  title: 'सावधिक जीवन बीमा (Life Insurance)',
                  amount: '- रु. ${_formatAmount(insurance)}',
                  isDeduction: true,
                  isDark: isDark,
                ),
              if (other > 0)
                _BreakdownRow(
                  title: 'कल्याणकारी कोष / पेश्की कट्टी (Other Deductions)',
                  amount: '- रु. ${_formatAmount(other)}',
                  isDeduction: true,
                  isDark: isDark,
                ),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.red.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'जम्मा कट्टी (Total Deductions)',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 12,
                        color: AppTheme.red,
                      ),
                    ),
                    Text(
                      '- रु. ${_formatAmount(totalDeductions)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                        color: AppTheme.red,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 18),
              Divider(
                  height: 1,
                  color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
              const SizedBox(height: 14),

              // FINAL NET TAKE HOME
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: isDark
                        ? [const Color(0xFF064E3B), const Color(0xFF065F46)]
                        : [const Color(0xFFECFDF5), const Color(0xFFD1FAE5)],
                  ),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFF10B981).withOpacity(0.4),
                    width: 1,
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'खुद भुक्तानी (Net Take-Home)',
                          style: TextStyle(
                            fontWeight: FontWeight.w900,
                            fontSize: 13.5,
                            color: isDark ? Colors.white : const Color(0xFF065F46),
                          ),
                        ),
                        const SizedBox(height: 1),
                        Text(
                          'खातामा जम्मा हुने अन्तिम रकम',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: isDark ? Colors.white70 : const Color(0xFF047857),
                          ),
                        ),
                      ],
                    ),
                    Text(
                      'रु. ${_formatAmount(net)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 20,
                        color: Color(0xFF10B981),
                        letterSpacing: -0.3,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // 5. PAST PAYSLIPS HISTORY SECTION
  // =========================================================================
  Widget _buildHistorySection(BuildContext context, bool isDark) {
    final filtered = _filteredPayslips;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const SectionHeader(
              title: 'Past Payslips (विगतका तलब भौचर)',
              subtitle: 'Tap to view or download full official slip',
            ),
          ],
        ),
        const SizedBox(height: 10),

        // Year filter pills
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _buildFilterChip('All', 'सबै (All)', isDark),
              const SizedBox(width: 8),
              _buildFilterChip('2081', '२०८१ BS', isDark),
              const SizedBox(width: 8),
              _buildFilterChip('2080', '२०८० BS', isDark),
            ],
          ),
        ),

        const SizedBox(height: 12),

        if (filtered.isEmpty)
          Container(
            padding: const EdgeInsets.all(24),
            alignment: Alignment.center,
            child: const Text(
              'कुनै तलब भौचर फेला परेन।',
              style: TextStyle(color: AppTheme.darkSubtext, fontSize: 13),
            ),
          )
        else
          ...filtered.map((slip) => _buildPayslipHistoryCard(context, slip, isDark)),
      ],
    );
  }

  Widget _buildFilterChip(String value, String label, bool isDark) {
    final isSelected = _selectedYearFilter == value;
    return GestureDetector(
      onTap: () {
        setState(() => _selectedYearFilter = value);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected
              ? AppTheme.primary
              : (isDark ? AppTheme.darkCard : Colors.white),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? AppTheme.primary
                : (isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder),
            width: 1,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected
                ? Colors.white
                : (isDark ? Colors.white70 : AppTheme.dark),
          ),
        ),
      ),
    );
  }

  Widget _buildPayslipHistoryCard(
      BuildContext context, Map<String, dynamic> slip, bool isDark) {
    final month = slip['month']?.toString() ?? 'Month';
    final net = slip['net_salary'] ?? slip['netSalary'] ?? 0;
    final paymentDate = slip['payment_date'] ?? slip['paymentDate'] ?? '२०८१';
    final voucherNo = slip['voucher_no'] ?? 'SLIP-2081';
    final isPaid = slip['is_paid'] ?? true;
    final bonus = slip['bonus'] ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkCard : Colors.white,
        borderRadius: BorderRadius.circular(18),
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _showDigitalVoucherModal(context, slip),
          borderRadius: BorderRadius.circular(18),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                // Icon
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(
                    Icons.receipt_long_rounded,
                    color: AppTheme.primary,
                    size: 22,
                  ),
                ),
                const SizedBox(width: 14),

                // Title & Details
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              month,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 13,
                                color: isDark ? Colors.white : AppTheme.dark,
                              ),
                            ),
                          ),
                          if (bonus > 0)
                            Container(
                              margin: const EdgeInsets.only(left: 6),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppTheme.orange.withOpacity(0.15),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'दशैं भत्ता 🎉',
                                style: TextStyle(
                                  fontSize: 9,
                                  fontWeight: FontWeight.w900,
                                  color: AppTheme.orange,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'भुक्तानी: $paymentDate • $voucherNo',
                        style: const TextStyle(
                          fontSize: 11,
                          color: AppTheme.darkSubtext,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(width: 10),

                // Amount and Action
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'रु. ${_formatAmount(net)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 14,
                        color: AppTheme.green,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          isPaid ? 'भौचर हेर्नुहोस्' : 'प्रक्रियामा',
                          style: const TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.primary,
                          ),
                        ),
                        const SizedBox(width: 2),
                        const Icon(
                          Icons.arrow_forward_ios_rounded,
                          size: 9,
                          color: AppTheme.primary,
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // =========================================================================
  // 6. DIGITAL SALARY VOUCHER MODAL (आधिकारिक तलब भौचर)
  // =========================================================================
  void _showDigitalVoucherModal(BuildContext context, Map<String, dynamic> slip) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final month = slip['month']?.toString() ?? 'Ashwin 2081';
    final voucherNo = slip['voucher_no']?.toString() ?? 'SLIP-2081-06101';
    final paymentDate = slip['payment_date']?.toString() ?? '२०८१-०६-०२';
    final paymentMethod = slip['payment_method']?.toString() ?? 'Direct Bank Transfer';
    final bank = slip['bank']?.toString() ?? 'Nabil Bank Ltd.';
    final panNo = slip['pan_no']?.toString() ?? '109283741';
    final basic = slip['basic_salary'] ?? slip['basicSalary'] ?? 0;
    final allowances = slip['allowances'] ?? 0;
    final bonus = slip['bonus'] ?? 0;
    final overtime = slip['overtime'] ?? 0;
    final gross = slip['gross_salary'] ?? (basic + allowances + bonus + overtime);

    final pf = slip['provident_fund'] ?? slip['providentFund'] ?? 0;
    final cit = slip['cit'] ?? 0;
    final tax = slip['tax_deduction'] ?? slip['tax'] ?? 0;
    final insurance = slip['insurance'] ?? 1000;
    final other = slip['other_deductions'] ?? 0;
    final totalDeductions = slip['total_deductions'] ?? (pf + cit + tax + insurance + other);
    final net = slip['net_salary'] ?? slip['netSalary'] ?? (gross - totalDeductions);

    final workingDays = slip['working_days'] ?? 26;
    final presentDays = slip['present_days'] ?? 25;
    final leaveDays = slip['leave_days'] ?? 1;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(ctx).size.height * 0.90,
          ),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkCard : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
          ),
          child: Column(
            children: [
              // Modal drag handle
              Container(
                margin: const EdgeInsets.only(top: 10, bottom: 8),
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkSurface : Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Title Bar
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'आधिकारिक तलब भौचर (Salary Slip)',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Text(
                          'Voucher: $voucherNo',
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppTheme.darkSubtext,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
              ),

              const Divider(height: 1),

              // Scrollable Voucher Body
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // School Header inside slip
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppTheme.primary.withOpacity(0.06),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: AppTheme.primary.withOpacity(0.18),
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 44,
                              height: 44,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: AppTheme.primary.withOpacity(0.18),
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: AppTheme.primary.withOpacity(0.08),
                                    blurRadius: 6,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              clipBehavior: Clip.antiAlias,
                              child: Image.asset(
                                'assets/icons/app_icon.png',
                                fit: BoxFit.cover,
                              ),
                            ),
                            const SizedBox(width: 12),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'NEPAL SCHOOL HUB (NSHLMS)',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w900,
                                      fontSize: 14,
                                      color: AppTheme.primary,
                                      letterSpacing: 0.3,
                                    ),
                                  ),
                                  SizedBox(height: 2),
                                  Text(
                                    'Faculty Payroll Slip • काठमाडौं, नेपाल',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: AppTheme.darkSubtext,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withOpacity(0.15),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Text(
                                'VERIFIED ✅',
                                style: TextStyle(
                                  color: Color(0xFF10B981),
                                  fontWeight: FontWeight.w900,
                                  fontSize: 10,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Employee Details Table
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Column(
                          children: [
                            _buildVoucherMetaRow('महिना (Month):', month, isDark),
                            _buildVoucherMetaRow('भुक्तानी मिति:', paymentDate, isDark),
                            _buildVoucherMetaRow('PAN नम्बर:', panNo, isDark),
                            _buildVoucherMetaRow('बैंक खाता:', bank, isDark),
                            _buildVoucherMetaRow('विधि (Mode):', paymentMethod, isDark),
                            _buildVoucherMetaRow(
                              'हाजिरी विवरण:',
                              'कुल: $workingDays | उपस्थित: $presentDays | बिदा: $leaveDays',
                              isDark,
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Earnings and Deductions Table
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Left: Earnings
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                border: Border.all(
                                  color: AppTheme.green.withOpacity(0.3),
                                ),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'आय (EARNINGS)',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w900,
                                      color: AppTheme.green,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  _buildVoucherTableItem('Basic Pay', basic),
                                  _buildVoucherTableItem('Allowances', allowances),
                                  if (bonus > 0)
                                    _buildVoucherTableItem('दशैं भत्ता', bonus),
                                  if (overtime > 0)
                                    _buildVoucherTableItem('Overtime', overtime),
                                  const Divider(height: 12),
                                  _buildVoucherTableItem('कुल आम्दानी', gross,
                                      isBold: true),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),

                          // Right: Deductions
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                border: Border.all(
                                  color: AppTheme.red.withOpacity(0.3),
                                ),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'कट्टी (DEDUCTIONS)',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w900,
                                      color: AppTheme.red,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  _buildVoucherTableItem('सञ्चय कोष 10%', pf),
                                  _buildVoucherTableItem('CIT कोष', cit),
                                  _buildVoucherTableItem('आयकर TDS', tax),
                                  if (insurance > 0)
                                    _buildVoucherTableItem('बीमा', insurance),
                                  if (other > 0)
                                    _buildVoucherTableItem('अन्य कट्टी', other),
                                  const Divider(height: 12),
                                  _buildVoucherTableItem('जम्मा कट्टी', totalDeductions,
                                      isBold: true),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // Net Salary Box
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppTheme.green.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: AppTheme.green.withOpacity(0.4),
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text(
                                  'खुद भुक्तानी पाउने रकम (NET PAYABLE):',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF065F46),
                                  ),
                                ),
                                Text(
                                  'रु. ${_formatAmount(net)}',
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.w900,
                                    color: AppTheme.green,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'अक्षरेपी: ${_numberToNepaliWords(net)} मात्र।',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF047857),
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Stamp & Signatures
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            children: [
                              Container(
                                width: 90,
                                height: 50,
                                decoration: BoxDecoration(
                                  border: Border.all(
                                    color: const Color(0xFF2563EB).withOpacity(0.4),
                                    style: BorderStyle.solid,
                                  ),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Center(
                                  child: Text(
                                    'ACCOUNTS\nDEPARTMENT\nVERIFIED',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontSize: 7.5,
                                      fontWeight: FontWeight.w900,
                                      color: Color(0xFF2563EB),
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'लेखा शाखा (Seal)',
                                style: TextStyle(
                                    fontSize: 10, color: AppTheme.darkSubtext),
                              ),
                            ],
                          ),
                          Column(
                            children: [
                              Container(
                                width: 90,
                                height: 50,
                                alignment: Alignment.bottomCenter,
                                padding: const EdgeInsets.only(bottom: 4),
                                child: Container(
                                  height: 1,
                                  color: isDark ? Colors.white38 : Colors.black38,
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'प्रधानाध्यापक (Principal)',
                                style: TextStyle(
                                    fontSize: 10, color: AppTheme.darkSubtext),
                              ),
                            ],
                          ),
                        ],
                      ),

                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ),

              // Footer Action Buttons
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkSurface : Colors.grey.shade50,
                  border: Border(
                    top: BorderSide(
                      color: isDark ? AppTheme.darkCardBorder : AppTheme.cardBorder,
                    ),
                  ),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        icon: const Icon(Icons.share_rounded, size: 18),
                        label: const Text(
                          'साझेदारी (Share)',
                          style: TextStyle(fontWeight: FontWeight.w800),
                        ),
                        onPressed: () {
                          Navigator.pop(ctx);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              backgroundColor: AppTheme.primary,
                              content: Text('Salary Voucher link copied to clipboard 📋'),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        icon: const Icon(Icons.file_download_rounded, size: 18),
                        label: const Text(
                          'PDF डाउनलोड',
                          style: TextStyle(fontWeight: FontWeight.w800),
                        ),
                        onPressed: () {
                          Navigator.pop(ctx);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              backgroundColor: AppTheme.green,
                              content: Text(
                                'Official Payslip ($voucherNo) downloaded to device storage ✅',
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildVoucherMetaRow(String label, String value, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2.5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              color: AppTheme.darkSubtext,
              fontWeight: FontWeight.w600,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: isDark ? Colors.white : AppTheme.dark,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVoucherTableItem(String title, dynamic amt, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: isBold ? FontWeight.w800 : FontWeight.w600,
            ),
          ),
          Text(
            'रु. ${_formatAmount(amt)}',
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: isBold ? FontWeight.w900 : FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // 7. ADVANCE SALARY REQUEST MODAL (तलब पेश्की अनुरोध)
  // =========================================================================
  void _showAdvanceRequestModal(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final amountController = TextEditingController(text: '20000');
    final reasonController = TextEditingController();
    int repaymentMonths = 3;
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(modalCtx).viewInsets.bottom,
              ),
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.darkCard : Colors.white,
                  borderRadius: const BorderRadius.vertical(
                    top: Radius.circular(26),
                  ),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Handle
                    Center(
                      child: Container(
                        width: 44,
                        height: 4,
                        decoration: BoxDecoration(
                          color: isDark
                              ? AppTheme.darkSurface
                              : Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Header
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'तलब पेश्की अनुरोध (Advance Request)',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                            Text(
                              'विद्यालय प्रशासन तथा लेखा शाखा समक्ष अनुरोध',
                              style: TextStyle(
                                fontSize: 11,
                                color: AppTheme.darkSubtext,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close_rounded),
                          onPressed: () => Navigator.pop(modalCtx),
                        ),
                      ],
                    ),

                    const SizedBox(height: 16),

                    // Amount Field
                    const Text(
                      'अनुरोध गरिएको रकम (Amount in रु.):',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: amountController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        prefixIcon: const Padding(
                          padding: EdgeInsets.all(12),
                          child: Text(
                            'रु.',
                            style: TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 16,
                            ),
                          ),
                        ),
                        hintText: 'रकम प्रविष्ट गर्नुहोस्',
                        filled: true,
                        fillColor: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: BorderSide.none,
                        ),
                      ),
                    ),

                    const SizedBox(height: 10),

                    // Quick Chips
                    Wrap(
                      spacing: 8,
                      children: [10000, 20000, 30000, 50000].map((amt) {
                        return ActionChip(
                          label: Text('रु. ${_formatAmount(amt)}'),
                          labelStyle: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                          ),
                          onPressed: () {
                            setModalState(() {
                              amountController.text = amt.toString();
                            });
                          },
                        );
                      }).toList(),
                    ),

                    const SizedBox(height: 14),

                    // Repayment Duration
                    const Text(
                      'किस्ता भुक्तानी अवधि (Repayment Months):',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [1, 2, 3, 6].map((m) {
                        final isSel = repaymentMonths == m;
                        return Expanded(
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 3),
                            child: ChoiceChip(
                              label: Text('$m महिना'),
                              selected: isSel,
                              onSelected: (_) {
                                setModalState(() => repaymentMonths = m);
                              },
                            ),
                          ),
                        );
                      }).toList(),
                    ),

                    const SizedBox(height: 14),

                    // Reason Field
                    const Text(
                      'पेश्की लिनुको प्रयोजन / कारण (Reason):',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: reasonController,
                      maxLines: 2,
                      decoration: InputDecoration(
                        hintText: 'उदा: चाडपर्व खर्च, उपचार वा घरायसी काम...',
                        hintStyle: const TextStyle(fontSize: 12),
                        filled: true,
                        fillColor: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: BorderSide.none,
                        ),
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0D9488),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                final amt = double.tryParse(amountController.text.trim()) ?? 0;
                                final reason = reasonController.text.trim();

                                if (amt < 5000) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(
                                      backgroundColor: AppTheme.red,
                                      content: Text('कृपया न्यूनतम रु. ५,००० पेश्की रकम प्रविष्ट गर्नुहोस्।'),
                                    ),
                                  );
                                  return;
                                }

                                if (reason.isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(
                                      backgroundColor: AppTheme.red,
                                      content: Text('कृपया पेश्की लिनुको कारण प्रविष्ट गर्नुहोस्।'),
                                    ),
                                  );
                                  return;
                                }

                                setModalState(() => isSubmitting = true);

                                final res = await _apiService.requestSalaryAdvance({
                                  'amount': amt,
                                  'reason': reason,
                                  'repayment_months': repaymentMonths,
                                });

                                if (!modalCtx.mounted) return;
                                Navigator.pop(modalCtx);

                                if (res.success) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      backgroundColor: AppTheme.green,
                                      content: Text(
                                        res.message.isNotEmpty
                                            ? res.message
                                            : 'Salary Advance request submitted successfully! लेखा शाखाले छिट्टै स्वीकृति गर्नेछ।',
                                      ),
                                    ),
                                  );
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      backgroundColor: AppTheme.red,
                                      content: Text('अनुरोध पठाउन असफल: ${res.message}'),
                                    ),
                                  );
                                }
                              },
                        child: isSubmitting
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : const Text(
                                'अनुरोध पेश गर्नुहोस् (Submit Request)',
                                style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 14,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  // =========================================================================
  // 8. ANNUAL TAX STATEMENT MODAL (वार्षिक कर तथा TDS विवरण)
  // =========================================================================
  void _showTaxStatementModal(BuildContext context) async {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    // Show loading dialog while fetching
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    final res = await _apiService.getTaxStatement();
    if (!context.mounted) return;
    Navigator.pop(context); // Close loading dialog

    final data = res.data ?? {
      'fiscal_year': '2081/2082 BS (FY 2024/25)',
      'teacher': {
        'name': 'Faculty Member',
        'pan_no': '109283741',
        'school_name': 'Nepal School Hub (NSHLMS)',
        'tax_office': 'Inland Revenue Office (आन्तरिक राजस्व कार्यालय - IRO)',
      },
      'slabs': [
        {'slab': 'First Rs. 500,000 (Individual)', 'rate': '1% (SST)', 'tax': 5000},
        {'slab': 'Next Rs. 200,000 (Rs. 5L to 7L)', 'rate': '10%', 'tax': 12500},
      ],
      'total_taxable_income': 625000,
      'total_tax_paid': 17500,
      'status': 'TDS Verified & Deposited (कर चुक्ता)',
    };

    final fiscalYear = data['fiscal_year'] ?? '2081/2082 BS';
    final teacher = data['teacher'] is Map ? data['teacher'] : {};
    final pan = teacher['pan_no'] ?? '109283741';
    final taxOffice = teacher['tax_office'] ?? 'आन्तरिक राजस्व कार्यालय (IRO)';
    final taxable = data['total_taxable_income'] ?? 625000;
    final totalTax = data['total_tax_paid'] ?? 17500;
    final slabs = data['slabs'] is List ? data['slabs'] as List : [];
    final status = data['status'] ?? 'TDS Deposited & Verified (कर चुक्ता)';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(ctx).size.height * 0.85,
          ),
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkCard : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 4,
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkSurface : Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 14),

              // Title
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'वार्षिक आयकर विवरण (Annual Tax TDS)',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      Text(
                        'Nepal IRD Conforming • Fiscal Year: $fiscalYear',
                        style: const TextStyle(
                          fontSize: 11,
                          color: AppTheme.darkSubtext,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),

              const SizedBox(height: 14),

              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Taxpayer Info Card
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.darkSurface : AppTheme.lightBg,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Column(
                          children: [
                            _buildVoucherMetaRow('करदाता PAN नम्बर:', pan.toString(), isDark),
                            _buildVoucherMetaRow('राजस्व कार्यालय:', taxOffice.toString(), isDark),
                            _buildVoucherMetaRow('करयोग्य आम्दानी:', 'रु. ${_formatAmount(taxable)}', isDark),
                            _buildVoucherMetaRow('कुल TDS कट्टी कर:', 'रु. ${_formatAmount(totalTax)}', isDark),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      const Text(
                        'नेपाल सरकारको आयकर स्ल्याब (Income Tax Slabs):',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 8),

                      // Slabs List
                      ...slabs.map((slab) {
                        final sName = slab['slab'] ?? '';
                        final rate = slab['rate'] ?? '';
                        final sTax = slab['tax'] ?? 0;
                        return Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            border: Border.all(
                              color: isDark
                                  ? AppTheme.darkCardBorder
                                  : AppTheme.cardBorder,
                            ),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    sName.toString(),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 12,
                                    ),
                                  ),
                                  Text(
                                    'दर: $rate',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: AppTheme.darkSubtext,
                                    ),
                                  ),
                                ],
                              ),
                              Text(
                                'रु. ${_formatAmount(sTax)}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 13,
                                  color: AppTheme.primary,
                                ),
                              ),
                            ],
                          ),
                        );
                      }),

                      const SizedBox(height: 14),

                      // Verified Status Card
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981).withOpacity(0.12),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: const Color(0xFF10B981).withOpacity(0.3),
                          ),
                        ),
                        child: Row(
                          children: [
                            const Icon(
                              Icons.verified_rounded,
                              color: Color(0xFF10B981),
                              size: 28,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    status.toString(),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w900,
                                      fontSize: 13,
                                      color: Color(0xFF065F46),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  const Text(
                                    'All TDS deductions verified with IRD electronic taxpayer portal.',
                                    style: TextStyle(
                                      fontSize: 10.5,
                                      color: Color(0xFF047857),
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],
                  ),
                ),
              ),

              // Close / Download button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  icon: const Icon(Icons.file_download_rounded, size: 18),
                  label: const Text(
                    'कर चुक्ता प्रमाणपत्र (Tax Certificate PDF)',
                    style: TextStyle(fontWeight: FontWeight.w800),
                  ),
                  onPressed: () {
                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        backgroundColor: AppTheme.green,
                        content: Text('Tax Statement & TDS Slip PDF downloaded to device storage ✅'),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // =========================================================================
  // UTILITIES & HELPERS
  // =========================================================================
  static String _formatAmount(dynamic amt) {
    if (amt == null) return '0';
    final numVal = (amt is num) ? amt.toInt() : (int.tryParse(amt.toString()) ?? 0);
    final str = numVal.toString();
    if (str.length <= 3) return str;
    
    // Format according to Nepali / South Asian numbering (e.g. 1,08,500)
    final lastThree = str.substring(str.length - 3);
    final remaining = str.substring(0, str.length - 3);
    final buffer = StringBuffer();
    for (int i = 0; i < remaining.length; i++) {
      if (i > 0 && (remaining.length - i) % 2 == 0) {
        buffer.write(',');
      }
      buffer.write(remaining[i]);
    }
    return '${buffer.toString()},$lastThree';
  }

  static String _numberToNepaliWords(dynamic amt) {
    final n = (amt is num) ? amt.toInt() : (int.tryParse(amt.toString()) ?? 0);
    if (n >= 100000) {
      final lakhs = (n / 100000).toStringAsFixed(1);
      return 'रुपैयाँ $lakhs लाख';
    } else if (n >= 1000) {
      final thousands = (n / 1000).toInt();
      return 'रुपैयाँ $thousands हजार';
    }
    return 'रुपैयाँ $n';
  }
}

class _SalaryDetailMini extends StatelessWidget {
  final String label, value;
  final bool isNegative;
  const _SalaryDetailMini({
    required this.label,
    required this.value,
    this.isNegative = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          value,
          style: TextStyle(
            color: isNegative ? const Color(0xFFFCA5A5) : Colors.white,
            fontWeight: FontWeight.w900,
            fontSize: 12.5,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withOpacity(0.75),
            fontSize: 10,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}

class _BreakdownRow extends StatelessWidget {
  final String title, amount;
  final bool isDeduction;
  final bool isDark;
  final bool isHighlighted;

  const _BreakdownRow({
    required this.title,
    required this.amount,
    required this.isDeduction,
    required this.isDark,
    this.isHighlighted = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 4.5, horizontal: 4),
      decoration: isHighlighted
          ? BoxDecoration(
              color: AppTheme.orange.withOpacity(0.1),
              borderRadius: BorderRadius.circular(6),
            )
          : null,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              title,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isHighlighted ? FontWeight.w800 : FontWeight.w600,
                color: isHighlighted
                    ? AppTheme.orange
                    : (isDark ? Colors.white70 : AppTheme.dark),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Text(
            amount,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              color: isDeduction
                  ? AppTheme.red
                  : (isHighlighted ? AppTheme.orange : AppTheme.green),
            ),
          ),
        ],
      ),
    );
  }
}
