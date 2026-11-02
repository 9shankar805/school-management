import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../services/api_service.dart';
import '../providers/api_provider.dart';
import 'main_shell.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailCtrl = TextEditingController(text: 'parent@school.edu');
  final _passCtrl = TextEditingController(text: '12345678');
  bool _obscure = true;
  bool _loading = false;
  bool _rememberMe = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadSavedPreferences();
  }

  Future<void> _loadSavedPreferences() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedEmail = prefs.getString('saved_parent_login_email');
      final remember = prefs.getBool('remember_parent_login') ?? true;
      if (savedEmail != null && savedEmail.isNotEmpty) {
        setState(() {
          _emailCtrl.text = savedEmail;
          _rememberMe = remember;
        });
      }
    } catch (_) {}
  }

  Future<void> _savePreferences() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('remember_parent_login', _rememberMe);
      if (_rememberMe) {
        await prefs.setString('saved_parent_login_email', _emailCtrl.text.trim());
      } else {
        await prefs.remove('saved_parent_login_email');
      }
    } catch (_) {}
  }

  void _login() async {
    final email = _emailCtrl.text.trim();
    final pass = _passCtrl.text;

    setState(() => _errorMessage = null);

    if (email.isEmpty) {
      setState(() => _errorMessage = 'Please enter your registered parent email or phone number.');
      return;
    }
    if (pass.isEmpty) {
      setState(() => _errorMessage = 'Please enter your account password.');
      return;
    }

    setState(() => _loading = true);

    final result = await ApiService.login(email, pass);

    if (!mounted) return;
    setState(() => _loading = false);

    if (result['success'] == true) {
      await _savePreferences();
      if (!mounted) return;

      context.read<ApiProvider>().loadAllData();

      final welcomeMsg = result['isDemo'] == true
          ? 'Connected in Demo Mode! Welcome Guardian.'
          : (result['data']?['user']?['name'] != null
              ? 'Welcome back, ${result['data']['user']['name']}!'
              : 'Welcome back to Nepal School Hub!');

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 10),
              Expanded(child: Text(welcomeMsg)),
            ],
          ),
          backgroundColor: AppTheme.green,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainShell()),
      );
    } else {
      setState(() {
        _errorMessage = result['message'] ?? 'Authentication failed. Please check your credentials.';
      });
    }
  }

  void _socialLogin(String provider) async {
    setState(() {
      _loading = true;
      _errorMessage = null;
    });

    final result = await ApiService.loginSocial(provider);

    if (!mounted) return;
    setState(() => _loading = false);

    if (result['success'] == true) {
      context.read<ApiProvider>().loadAllData();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Signed in successfully via $provider'),
          backgroundColor: AppTheme.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainShell()),
      );
    } else {
      setState(() => _errorMessage = result['message'] ?? 'Social login failed.');
    }
  }

  void _quickFillDemo() {
    setState(() {
      _emailCtrl.text = 'parent@school.edu';
      _passCtrl.text = '12345678';
      _errorMessage = null;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Demo parent credentials filled. Click "Sign In" to proceed.'),
        duration: Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _instantDemoLogin() async {
    _quickFillDemo();
    _login();
  }

  void _showForgotPasswordSheet(BuildContext context) {
    final resetCtrl = TextEditingController(text: _emailCtrl.text);
    bool resetLoading = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(24, 24, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Reset Password', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const SizedBox(height: 8),
              const Text(
                'Enter your registered parent email address. We will send a secure password reset link with instructions.',
                style: TextStyle(fontSize: 12, color: AppTheme.grey),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: resetCtrl,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'Registered Parent Email',
                  prefixIcon: Icon(Icons.email_outlined, color: AppTheme.grey),
                  filled: true,
                  fillColor: AppTheme.lightBg,
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: resetLoading
                      ? null
                      : () async {
                          final email = resetCtrl.text.trim();
                          if (email.isEmpty) return;
                          setSheetState(() => resetLoading = true);
                          final success = await ApiService.forgotPassword(email);
                          if (ctx.mounted) Navigator.pop(ctx);
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(success
                                    ? 'Password reset link sent to $email!'
                                    : 'Unable to send reset instructions. Please check the email address.'),
                                backgroundColor: success ? AppTheme.green : AppTheme.orange,
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          }
                        },
                  child: resetLoading
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text('Send Reset Instructions'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showServerConfigDialog() {
    final ctrl = TextEditingController(text: ApiService.baseUrl);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.dns_rounded, color: AppTheme.primary),
            SizedBox(width: 8),
            Text('Server Endpoint', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Configure the Nepal School Hub backend API base URL:',
              style: TextStyle(fontSize: 12, color: AppTheme.grey),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: ctrl,
              decoration: const InputDecoration(
                labelText: 'Base API URL',
                prefixIcon: Icon(Icons.link, size: 20),
                filled: true,
                fillColor: AppTheme.lightBg,
              ),
            ),
            const SizedBox(height: 12),
            const Text('Presets:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.grey)),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                ActionChip(
                  label: const Text('Production', style: TextStyle(fontSize: 11)),
                  onPressed: () => ctrl.text = ApiService.defaultBaseUrl,
                ),
                ActionChip(
                  label: const Text('Localhost (8000)', style: TextStyle(fontSize: 11)),
                  onPressed: () => ctrl.text = 'http://127.0.0.1:8000/api',
                ),
                ActionChip(
                  label: const Text('Android (10.0.2.2)', style: TextStyle(fontSize: 11)),
                  onPressed: () => ctrl.text = 'http://10.0.2.2:8000/api',
                ),
              ],
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              final newUrl = ctrl.text.trim();
              if (newUrl.isNotEmpty) {
                await ApiService.setCustomBaseUrl(newUrl);
              }
              if (ctx.mounted) {
                Navigator.pop(ctx);
              }
              if (mounted && newUrl.isNotEmpty) {
                setState(() {});
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Connected API Server: $newUrl'), backgroundColor: AppTheme.green),
                );
              }
            },
            child: const Text('Save & Reconnect'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Bar with Academic Session & Settings
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryLight,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text('🇳🇵 ', style: TextStyle(fontSize: 11)),
                        Text(
                          'Session 2081-82 (२०८१)',
                          style: TextStyle(
                            color: AppTheme.primaryDark,
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.dns_outlined, color: AppTheme.grey, size: 20),
                        tooltip: 'Configure Backend Server URL',
                        onPressed: _showServerConfigDialog,
                      ),
                      IconButton(
                        icon: const Icon(Icons.help_outline_rounded, color: AppTheme.grey, size: 20),
                        tooltip: 'Parent Helpdesk Support',
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Parent Helpdesk Hotline: 📞 01-5970000 • support@nepalschoolhub.com'),
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // App Logo & Branding
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    gradient: AppTheme.primaryGradient,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: AppTheme.primary.withValues(alpha: 0.35),
                        blurRadius: 20,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Icon(Icons.family_restroom_rounded, size: 44, color: Colors.white),
                ),
              ).animate().scale(duration: 500.ms, curve: Curves.elasticOut),
              const SizedBox(height: 20),

              Center(
                child: Column(
                  children: [
                    Text(
                      'Welcome to ParentLink 👋',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w900,
                        color: isDark ? Colors.white : AppTheme.dark,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Nepal School Hub — Guardian Portal',
                      style: TextStyle(fontSize: 13, color: AppTheme.grey, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Server indicator banner
              GestureDetector(
                onTap: _showServerConfigDialog,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkCard : AppTheme.lightBg,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppTheme.grey.withValues(alpha: 0.2)),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: AppTheme.green,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Backend: ${ApiService.baseUrl.replaceFirst('https://', '').replaceFirst('http://', '')}',
                          style: const TextStyle(fontSize: 11, color: AppTheme.grey, fontWeight: FontWeight.w600),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const Icon(Icons.edit_outlined, size: 14, color: AppTheme.grey),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Error banner if any
              if (_errorMessage != null)
                Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  decoration: BoxDecoration(
                    color: AppTheme.redLight,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppTheme.red.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.error_outline_rounded, color: AppTheme.red, size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              _errorMessage!,
                              style: const TextStyle(fontSize: 12, color: AppTheme.red, fontWeight: FontWeight.w700),
                            ),
                            if (_errorMessage!.contains('server') || _errorMessage!.contains('connect')) ...[
                              const SizedBox(height: 6),
                              GestureDetector(
                                onTap: _instantDemoLogin,
                                child: const Text(
                                  'Tap here to login in Offline Demo Mode',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: AppTheme.primary,
                                    fontWeight: FontWeight.w800,
                                    decoration: TextDecoration.underline,
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                      IconButton(
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                        icon: const Icon(Icons.close, size: 16, color: AppTheme.red),
                        onPressed: () => setState(() => _errorMessage = null),
                      ),
                    ],
                  ),
                ).animate().shake(duration: 400.ms),

              // 1-Tap Quick Demo credentials row
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.primaryLight,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppTheme.primary.withValues(alpha: 0.2)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.flash_on_rounded, color: AppTheme.primary, size: 22),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Quick Demo Parent Access',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppTheme.primaryDark),
                          ),
                          Text(
                            'parent@school.edu • Pass: 12345678',
                            style: TextStyle(fontSize: 11, color: AppTheme.primary),
                          ),
                        ],
                      ),
                    ),
                    ElevatedButton(
                      onPressed: _loading ? null : _instantDemoLogin,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        elevation: 0,
                      ),
                      child: const Text('1-Tap Login', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              _buildField(
                'Parent Email or Phone',
                _emailCtrl,
                Icons.email_outlined,
                keyboardType: TextInputType.emailAddress,
                hint: 'parent@school.edu or 98xxxxxxxx',
              ),
              const SizedBox(height: 16),
              _buildField(
                'Password',
                _passCtrl,
                Icons.lock_outline,
                obscure: _obscure,
                hint: 'Enter your password',
                suffix: IconButton(
                  icon: Icon(_obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined, color: AppTheme.grey, size: 20),
                  onPressed: () => setState(() => _obscure = !_obscure),
                ),
              ),
              const SizedBox(height: 12),

              Row(
                children: [
                  GestureDetector(
                    onTap: () => setState(() => _rememberMe = !_rememberMe),
                    child: Row(
                      children: [
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          width: 20,
                          height: 20,
                          decoration: BoxDecoration(
                            color: _rememberMe ? AppTheme.primary : Colors.transparent,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: _rememberMe ? AppTheme.primary : AppTheme.grey.withValues(alpha: 0.4),
                              width: 2,
                            ),
                          ),
                          child: _rememberMe ? const Icon(Icons.check, size: 13, color: Colors.white) : null,
                        ),
                        const SizedBox(width: 8),
                        const Text('Remember me', style: TextStyle(fontSize: 13, color: AppTheme.grey, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                  const Spacer(),
                  TextButton(
                    onPressed: () => _showForgotPasswordSheet(context),
                    child: const Text('Forgot Password?', style: TextStyle(color: AppTheme.primary, fontWeight: FontWeight.w800, fontSize: 13)),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton(
                  onPressed: _loading ? null : _login,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primary,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  ),
                  child: _loading
                      ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                      : const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.login_rounded, size: 20, color: Colors.white),
                            SizedBox(width: 8),
                            Text('Sign In to Guardian Portal', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Colors.white)),
                          ],
                        ),
                ),
              ),
              const SizedBox(height: 24),

              const Row(
                children: [
                  Expanded(child: Divider()),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 12),
                    child: Text('or sign in with', style: TextStyle(color: AppTheme.grey, fontSize: 12, fontWeight: FontWeight.w600)),
                  ),
                  Expanded(child: Divider()),
                ],
              ),
              const SizedBox(height: 18),

              Row(
                children: [
                  Expanded(
                    child: _SocialBtn(
                      label: 'Google Workspace',
                      icon: Icons.g_mobiledata_rounded,
                      color: Colors.red,
                      onTap: () => _socialLogin('google'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _SocialBtn(
                      label: 'Microsoft 365',
                      icon: Icons.window_rounded,
                      color: AppTheme.blue,
                      onTap: () => _socialLogin('microsoft'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 28),

              const Center(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text("Need parent account activation? ", style: TextStyle(color: AppTheme.grey, fontSize: 12)),
                    Text('Contact School Admin', style: TextStyle(color: AppTheme.primary, fontWeight: FontWeight.w800, fontSize: 12)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildField(
    String label,
    TextEditingController ctrl,
    IconData icon, {
    bool obscure = false,
    Widget? suffix,
    TextInputType? keyboardType,
    String? hint,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppTheme.dark)),
        const SizedBox(height: 8),
        TextField(
          controller: ctrl,
          obscureText: obscure,
          keyboardType: keyboardType,
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(color: AppTheme.grey, fontSize: 13),
            prefixIcon: Icon(icon, color: AppTheme.grey, size: 20),
            suffixIcon: suffix,
            filled: true,
            fillColor: AppTheme.lightBg,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: BorderSide(color: AppTheme.grey.withValues(alpha: 0.15)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: const BorderSide(color: AppTheme.primary, width: 1.5),
            ),
          ),
        ),
      ],
    );
  }
}

class _SocialBtn extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  const _SocialBtn({
    required this.label,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.grey.withValues(alpha: 0.2)),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 20),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                label,
                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppTheme.dark),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
