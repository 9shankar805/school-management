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

    return Scaffold(
      appBar: AppBar(
        title: const Text('Fee Ledger & Payments'),
        automaticallyImplyLeading: false,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 0),
            child: GradientCard(
              colors: const [Color(0xFF6C3CE9), Color(0xFF2E86DE)],
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
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          totalPending > 0 ? 'Payment Due' : 'All Clear ✅',
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
                        ElevatedButton(
                          onPressed: () => _showPaymentSheet(context, pendingList.first),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: AppTheme.primary,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                          ),
                          child: const Text('Pay Now', style: TextStyle(fontWeight: FontWeight.w900)),
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
                  const Text('Payment Gateway', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 8),
              Text(fee.title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppTheme.dark)),
              Text('Invoice: ${fee.invoiceNumber} • Due: ${fee.dueDate}', style: const TextStyle(fontSize: 12, color: AppTheme.grey)),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.lightBg,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Payable Amount:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                    Text('NPR ${fee.amount.toStringAsFixed(2)}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppTheme.primary)),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const Text('Select Payment Option:', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
              const SizedBox(height: 8),
              _PaymentOption(
                name: 'eSewa Mobile Wallet',
                subtitle: 'Direct instant merchant payment',
                iconColor: const Color(0xFF60BB46),
                isSelected: selectedMethod == 'eSewa',
                onTap: () => setSheetState(() => selectedMethod = 'eSewa'),
              ),
              _PaymentOption(
                name: 'Khalti Digital Wallet',
                subtitle: 'Pay via Khalti PIN or banking',
                iconColor: const Color(0xFF5D2E8E),
                isSelected: selectedMethod == 'Khalti',
                onTap: () => setSheetState(() => selectedMethod = 'Khalti'),
              ),
              _PaymentOption(
                name: 'Fonepay Interbank QR',
                subtitle: 'Scan and pay via any Nepal bank app',
                iconColor: const Color(0xFFDC143C),
                isSelected: selectedMethod == 'Fonepay',
                onTap: () => setSheetState(() => selectedMethod = 'Fonepay'),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
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
                  },
                  child: Text('Proceed with $selectedMethod'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showReceiptDialog(BuildContext context, FeeRecord fee) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text('Payment Receipt', style: TextStyle(fontWeight: FontWeight.w900)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 48,
                height: 48,
                decoration: const BoxDecoration(
                  color: AppTheme.greenLight,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle, color: AppTheme.green, size: 28),
              ),
            ),
            const SizedBox(height: 12),
            Center(
              child: Text(
                'NPR ${fee.amount.toStringAsFixed(2)}',
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: AppTheme.green),
              ),
            ),
            const Center(
              child: Text('Payment Verified', style: TextStyle(fontSize: 11, color: AppTheme.grey)),
            ),
            const Divider(height: 24),
            _ReceiptLine(label: 'Invoice No', value: fee.invoiceNumber),
            _ReceiptLine(label: 'Description', value: fee.title),
            _ReceiptLine(label: 'Paid Date', value: fee.paidDate.isNotEmpty ? fee.paidDate : '2026-08-25'),
            _ReceiptLine(label: 'Payment Method', value: fee.paymentMethod),
            _ReceiptLine(label: 'Transaction Ref', value: 'TXN-98472910'),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Close')),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Downloading verified fee receipt PDF...')),
              );
            },
            icon: const Icon(Icons.file_download_outlined, size: 16),
            label: const Text('Save PDF'),
          ),
        ],
      ),
    );
  }
}

class _FeeCard extends StatelessWidget {
  final FeeRecord fee;
  final VoidCallback onPay, onReceipt;

  const _FeeCard({required this.fee, required this.onPay, required this.onReceipt});

  @override
  Widget build(BuildContext context) {
    final isPaid = fee.status.toLowerCase() == 'paid';

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
                    color: isPaid ? AppTheme.greenLight : AppTheme.orangeLight,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Icon(
                    isPaid ? Icons.receipt_rounded : Icons.pending_actions_rounded,
                    color: isPaid ? AppTheme.green : AppTheme.orange,
                    size: 22,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(fee.title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800)),
                      Text(fee.invoiceNumber, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    ],
                  ),
                ),
                StatusBadge(
                  label: isPaid ? 'PAID' : 'PENDING',
                  color: isPaid ? AppTheme.green : AppTheme.orange,
                ),
              ],
            ),
            const Divider(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(isPaid ? 'Amount Paid' : 'Due Date: ${fee.dueDate}', style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
                    Text(
                      'NPR ${fee.amount.toStringAsFixed(2)}',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: isPaid ? AppTheme.green : AppTheme.dark,
                      ),
                    ),
                  ],
                ),
                if (isPaid)
                  TextButton.icon(
                    onPressed: onReceipt,
                    icon: const Icon(Icons.receipt_outlined, size: 16),
                    label: const Text('Receipt'),
                  )
                else
                  ElevatedButton(
                    onPressed: onPay,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      minimumSize: const Size(80, 36),
                    ),
                    child: const Text('Pay Now', style: TextStyle(fontSize: 12)),
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
  final String name, subtitle;
  final Color iconColor;
  final bool isSelected;
  final VoidCallback onTap;

  const _PaymentOption({
    required this.name,
    required this.subtitle,
    required this.iconColor,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.primaryLight.withOpacity(0.5) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppTheme.primary : const Color(0xFFE5E9F0),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(color: iconColor.withOpacity(0.15), shape: BoxShape.circle),
              child: Icon(Icons.account_balance_wallet, color: iconColor, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                  Text(subtitle, style: const TextStyle(fontSize: 10, color: AppTheme.grey)),
                ],
              ),
            ),
            Icon(
              isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
              color: isSelected ? AppTheme.primary : AppTheme.grey,
              size: 20,
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
          Text(label, style: const TextStyle(fontSize: 11, color: AppTheme.grey)),
          Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}
