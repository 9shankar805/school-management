import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import '../theme/app_theme.dart';
import '../data/app_data.dart';
import '../providers/api_provider.dart';
import '../services/api_service.dart';
import '../widgets/common_widgets.dart';

class FeesScreen extends StatefulWidget {
  const FeesScreen({super.key});
  @override
  State<FeesScreen> createState() => _FeesScreenState();
}

class _FeesScreenState extends State<FeesScreen> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final api = context.watch<ApiProvider>();
    final fees = api.fees;
    final child = api.selectedChild;

    final pendingList = fees.where((f) => f.status.toLowerCase() == 'pending').toList();
    final paidList = fees.where((f) => f.status.toLowerCase() == 'paid').toList();

    final double totalPending = pendingList.fold(0.0, (sum, f) => sum + f.dueAmount);
    final double totalPaid = paidList.fold(0.0, (sum, f) => sum + f.paidAmount);

    final canPop = Navigator.canPop(context);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Fee Ledger & Payments'),
        automaticallyImplyLeading: false,
        leading: canPop
            ? CircleIconButton(
                icon: Icons.arrow_back_ios_new,
                onTap: () => Navigator.pop(context),
                size: 38,
              )
            : null,
        leadingWidth: canPop ? 60 : null,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
            child: GradientCard(
              colors: const [Color(0xFF4F46E5), Color(0xFF2563EB)],
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Account Ledger • ${child.name}',
                        style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: Text(
                          totalPending > 0 ? 'Payment Due' : 'All Settled ✅',
                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  const Text('Total Outstanding Due', style: TextStyle(color: Colors.white70, fontSize: 13)),
                  const SizedBox(height: 4),
                  Text(
                    'NPR ${totalPending.toStringAsFixed(2)}',
                    style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Total Paid (Academic Year)', style: TextStyle(color: Colors.white70, fontSize: 10)),
                          Text('NPR ${totalPaid.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w800)),
                        ],
                      ),
                      if (totalPending > 0)
                        ElevatedButton.icon(
                          onPressed: () => _showPaymentSheet(context, pendingList.first),
                          icon: const Icon(Icons.payment, size: 16),
                          label: const Text('Pay Dues', style: TextStyle(fontWeight: FontWeight.w900)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: const Color(0xFF4F46E5),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            elevation: 0,
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ).animate().fadeIn().slideY(begin: 0.05),
          ),
          const SizedBox(height: 12),

          TabBar(
            controller: _tabCtrl,
            labelColor: AppTheme.primary,
            unselectedLabelColor: AppTheme.grey,
            indicatorColor: AppTheme.primary,
            indicatorWeight: 3,
            labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
            tabs: [
              Tab(text: 'Pending Invoices (${pendingList.length})'),
              Tab(text: 'Payment History (${paidList.length})'),
            ],
          ),

          Expanded(
            child: TabBarView(
              controller: _tabCtrl,
              children: [
                _buildInvoiceList(pendingList, isPending: true),
                _buildInvoiceList(paidList, isPending: false),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInvoiceList(List<FeeRecord> list, {required bool isPending}) {
    if (list.isEmpty) {
      return EmptyStateWidget(
        icon: isPending ? Icons.verified_outlined : Icons.receipt_long_outlined,
        title: isPending ? 'No pending fees! 🎉' : 'No past payment records',
        subtitle: isPending ? 'All school fee dues are fully settled.' : 'Your receipts will appear here once paid.',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 100),
      itemCount: list.length,
      itemBuilder: (context, idx) {
        final f = list[idx];
        return _FeeCard(
          fee: f,
          onPay: () => _showPaymentSheet(context, f),
          onReceipt: () => _showReceiptDialog(context, f),
        ).animate().fadeIn(delay: (idx * 50).ms);
      },
    );
  }

  void _showPaymentSheet(BuildContext context, FeeRecord fee) {
    String selectedMethod = 'eSewa';
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Payment Gateway (Nepal)', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 6),
              Text(fee.title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppTheme.dark)),
              Text('Invoice: ${fee.invoiceNumber} • Due: ${fee.dueDate}', style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Total Amount Payable:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                    Text(
                      'NPR ${fee.amount.toStringAsFixed(2)}',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppTheme.primary),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const Text('Select Instant Payment Option:', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
              const SizedBox(height: 8),
              _PaymentOption(
                name: 'eSewa Mobile Wallet',
                subtitle: 'Direct instant merchant payment',
                brandColor: const Color(0xFF60BB46),
                badgeText: 'Instant',
                isSelected: selectedMethod == 'eSewa',
                onTap: () => setSheetState(() => selectedMethod = 'eSewa'),
              ),
              _PaymentOption(
                name: 'Khalti Digital Wallet',
                subtitle: 'Pay via Khalti PIN or mobile banking',
                brandColor: const Color(0xFF5D2E8E),
                badgeText: 'Cashback 2%',
                isSelected: selectedMethod == 'Khalti',
                onTap: () => setSheetState(() => selectedMethod = 'Khalti'),
              ),
              _PaymentOption(
                name: 'Fonepay Dynamic QR',
                subtitle: 'Scan with Global IME, Nabil, NIC Asia & 50+ banks',
                brandColor: const Color(0xFFDC2626),
                badgeText: 'All Nepal Banks',
                isSelected: selectedMethod == 'Fonepay',
                onTap: () => setSheetState(() => selectedMethod = 'Fonepay'),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: selectedMethod == 'eSewa'
                        ? const Color(0xFF60BB46)
                        : (selectedMethod == 'Khalti' ? const Color(0xFF5D2E8E) : const Color(0xFFDC2626)),
                    foregroundColor: Colors.white,
                  ),
                  onPressed: () async {
                    Navigator.pop(ctx);
                    if (selectedMethod == 'Fonepay') {
                      _showFonepayQrModal(context, fee);
                    } else {
                      await ApiService.payFee(fee.id ?? 1, method: selectedMethod, amount: fee.amount);
                      if (context.mounted) {
                        context.read<ApiProvider>().loadChildSpecificData();
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Payment of NPR ${fee.amount.toInt()} via $selectedMethod successful!'),
                            backgroundColor: AppTheme.green,
                          ),
                        );
                      }
                    }
                  },
                  child: Text('Pay with $selectedMethod', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showFonepayQrModal(BuildContext context, FeeRecord fee) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.qr_code_scanner, color: Color(0xFFDC2626)),
                    SizedBox(width: 8),
                    Text('Fonepay Scan & Pay', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
                  ],
                ),
                IconButton(icon: const Icon(Icons.close, size: 20), onPressed: () => Navigator.pop(context)),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFCBD5E1), width: 1.5),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10),
                ],
              ),
              child: Column(
                children: [
                  Container(
                    width: 170,
                    height: 170,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: const Center(
                      child: Icon(Icons.qr_code_2_rounded, size: 140, color: Color(0xFF0F172A)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text('Nepal School Hub • Fee Payment', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                  Text('NPR ${fee.amount.toStringAsFixed(2)}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFFDC2626))),
                  const SizedBox(height: 4),
                  const Text('Scan with Mobile Banking or Fonepay App', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                ],
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF047857)),
                onPressed: () async {
                  Navigator.pop(context);
                  await ApiService.payFee(fee.id ?? 1, method: 'Fonepay QR', amount: fee.amount);
                  if (context.mounted) {
                    context.read<ApiProvider>().loadChildSpecificData();
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Fonepay QR payment of NPR ${fee.amount.toInt()} confirmed!'),
                        backgroundColor: AppTheme.green,
                      ),
                    );
                  }
                },
                icon: const Icon(Icons.check_circle, size: 18),
                label: const Text('Simulate Scan Confirmation'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showReceiptDialog(BuildContext context, FeeRecord fee) {
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
                      Text('Official Fee Receipt', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                      Text('Tax Invoice • Nepal School Hub', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Center(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'NPR ${fee.amount.toStringAsFixed(2)}',
                  style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: Color(0xFF047857)),
                ),
              ),
            ),
            const Divider(height: 24),
            _ReceiptLine(label: 'Invoice Number', value: fee.invoiceNumber),
            _ReceiptLine(label: 'Description', value: fee.title),
            _ReceiptLine(label: 'Paid Date', value: fee.paidDate.isNotEmpty ? fee.paidDate : '2026-08-25'),
            _ReceiptLine(label: 'Payment Method', value: fee.paymentMethod),
            const _ReceiptLine(label: 'Transaction ID', value: 'TXN-NSH-98472910'),
            const _ReceiptLine(label: 'Status', value: 'Settled & Verified ✅'),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Downloading verified fee receipt PDF with tax stamp...'),
                  backgroundColor: Color(0xFF047857),
                ),
              );
            },
            icon: const Icon(Icons.download, size: 16),
            label: const Text('Save PDF Receipt'),
          ),
        ],
      ),
    );
  }
}

class _FeeCard extends StatelessWidget {
  final FeeRecord fee;
  final VoidCallback onPay;
  final VoidCallback onReceipt;

  const _FeeCard({required this.fee, required this.onPay, required this.onReceipt});

  @override
  Widget build(BuildContext context) {
    final isPending = fee.status.toLowerCase() == 'pending';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: AppCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isPending ? AppTheme.redLight : AppTheme.greenLight,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    fee.invoiceNumber,
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: isPending ? AppTheme.red : AppTheme.green,
                    ),
                  ),
                ),
                StatusBadge(
                  label: fee.status.toUpperCase(),
                  color: isPending ? AppTheme.red : AppTheme.green,
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(fee.title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
            const SizedBox(height: 2),
            Text(
              isPending ? 'Due by ${fee.dueDate}' : 'Paid on ${fee.paidDate} via ${fee.paymentMethod}',
              style: const TextStyle(fontSize: 11, color: AppTheme.grey),
            ),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Amount', style: TextStyle(fontSize: 10, color: AppTheme.grey)),
                    Text(
                      'NPR ${fee.amount.toStringAsFixed(2)}',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: AppTheme.dark),
                    ),
                  ],
                ),
                if (isPending)
                  ElevatedButton(
                    onPressed: onPay,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Pay Now', style: TextStyle(fontWeight: FontWeight.w800)),
                  )
                else
                  OutlinedButton.icon(
                    onPressed: onReceipt,
                    icon: const Icon(Icons.receipt_outlined, size: 14),
                    label: const Text('Receipt', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                    style: OutlinedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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

class _PaymentOption extends StatelessWidget {
  final String name, subtitle, badgeText;
  final Color brandColor;
  final bool isSelected;
  final VoidCallback onTap;

  const _PaymentOption({
    required this.name,
    required this.subtitle,
    required this.brandColor,
    required this.badgeText,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: isSelected ? brandColor.withValues(alpha: 0.08) : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: isSelected ? brandColor : const Color(0xFFE2E8F0), width: isSelected ? 2 : 1),
        ),
        child: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: brandColor.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(Icons.account_balance_wallet_rounded, color: brandColor, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: brandColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(badgeText, style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: brandColor)),
                      ),
                    ],
                  ),
                  Text(subtitle, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                ],
              ),
            ),
            Icon(
              isSelected ? Icons.check_circle_rounded : Icons.radio_button_off_rounded,
              color: isSelected ? brandColor : AppTheme.grey,
            ),
          ],
        ),
      ),
    );
  }
}

class _ReceiptLine extends StatelessWidget {
  final String label, value;
  const _ReceiptLine({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 11.5, color: AppTheme.grey, fontWeight: FontWeight.w600)),
          Text(value, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
        ],
      ),
    );
  }
}
