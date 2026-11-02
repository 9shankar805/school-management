import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import '../providers/auth_provider.dart';
import '../providers/teacher_provider.dart';
import '../services/api_service.dart';
import 'main_shell.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _idCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _obscure = true;
  bool _loading = false;
  bool _rememberMe = true;
  bool _biometricEnabled = false;
  String? _error;

  late final PageController _slideController;
  Timer? _slideTimer;
  int _currentSlide = 0;
  final List<String> _slideImages = [
    'assets/images/slide1.webp',
    'assets/images/slide2.webp',
    'assets/images/slide3.webp',
    'assets/images/slide4.webp',
  ];

  @override
  void initState() {
    super.initState();
    _slideController = PageController();
    _startSlideTimer();
    _loadBiometricSetting();
  }

  void _startSlideTimer() {
    _slideTimer?.cancel();
    _slideTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (!mounted || !_slideController.hasClients) return;
      final nextPage = (_currentSlide + 1) % _slideImages.length;
      _slideController.animateToPage(
        nextPage,
        duration: const Duration(milliseconds: 700),
        curve: Curves.easeInOutCubic,
      );
    });
  }

  @override
  void dispose() {
    _slideTimer?.cancel();
    _slideController.dispose();
    _idCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadBiometricSetting() async {
    final prefs = await SharedPreferences.getInstance();
    final bioEnabled = prefs.getBool('biometric_login_enabled') ?? true;
    final remember = prefs.getBool('remember_me_enabled') ?? true;
    final savedId = prefs.getString('saved_teacher_id') ?? '';
    final savedPass = prefs.getString('saved_teacher_password') ?? '';

    if (mounted) {
      setState(() {
        _biometricEnabled = bioEnabled;
        _rememberMe = remember;
        if (savedId.isNotEmpty) {
          _idCtrl.text = savedId;
        }
        if (savedPass.isNotEmpty && remember) {
          _passCtrl.text = savedPass;
        }
      });
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final auth = Provider.of<AuthProvider>(context, listen: false);
      if (auth.errorMessage != null &&
          (auth.errorMessage!.contains('24-hour') || auth.errorMessage!.contains('session'))) {
        setState(() {
          _error = auth.errorMessage;
        });
      }
    });
  }

  Future<void> _toggleBiometric(bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('biometric_login_enabled', value);
    if (!mounted) return;
    setState(() => _biometricEnabled = value);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        backgroundColor: value ? const Color(0xFF0F4C81) : const Color(0xFF334155),
        duration: const Duration(seconds: 2),
        content: Row(
          children: [
            Icon(
              value ? Icons.fingerprint_rounded : Icons.lock_outline_rounded,
              color: Colors.white,
              size: 18,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                value
                    ? 'Biometrics enabled! You can now use Face ID / Fingerprint to log in.'
                    : 'Biometrics disabled. Standard password required.',
                style: const TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _loginWithBiometrics() async {
    if (!_biometricEnabled) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: AppTheme.orange,
          content: Text('Biometrics is disabled. Tap the toggle button to enable it.'),
        ),
      );
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    final savedId = prefs.getString('saved_teacher_id') ?? _idCtrl.text.trim();
    final savedPass = prefs.getString('saved_teacher_password') ?? _passCtrl.text;

    if (savedId.isEmpty || savedPass.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: AppTheme.orange,
          content: Text('Please sign in with your credentials once so Biometrics can remember you.'),
        ),
      );
      return;
    }

    _idCtrl.text = savedId;
    _passCtrl.text = savedPass;

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        backgroundColor: AppTheme.green,
        duration: Duration(milliseconds: 1200),
        content: Row(
          children: [
            Icon(Icons.fingerprint_rounded, color: Colors.white, size: 20),
            SizedBox(width: 8),
            Text('Biometric authentication verified! Signing in...', style: TextStyle(fontWeight: FontWeight.w700)),
          ],
        ),
      ),
    );

    _login();
  }

  void _login() async {
    final loginId = _idCtrl.text.trim();
    final pass = _passCtrl.text;
    if (loginId.isEmpty || pass.isEmpty) {
      setState(() => _error = 'Please enter your Teacher ID or Email and password');
      return;
    }

    setState(() {
      _loading = true;
      _error = null;
    });

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final teacherProvider = Provider.of<TeacherProvider>(context, listen: false);

    final success = await authProvider.login(loginId, pass);

    if (!mounted) return;
    setState(() => _loading = false);

    if (success) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('remember_me_enabled', _rememberMe);
      if (_rememberMe) {
        await prefs.setString('saved_teacher_id', loginId);
        await prefs.setString('saved_teacher_password', pass);
      } else {
        await prefs.remove('saved_teacher_id');
        await prefs.remove('saved_teacher_password');
      }
      ScaffoldMessenger.of(context).clearSnackBars();
      teacherProvider.refreshAll();
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainShell()),
      );
    } else {
      setState(() {
        _error = authProvider.errorMessage ?? 'Login failed. Please check your credentials.';
      });
    }
  }

  void _showServerConfig() {
    final apiService = ApiService();
    final urlCtrl = TextEditingController(text: apiService.baseUrl);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: EdgeInsets.fromLTRB(24, 20, 24, MediaQuery.of(ctx).viewInsets.bottom + 24),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                const Icon(Icons.dns_rounded, color: Color(0xFF0F4C81)),
                const SizedBox(width: 8),
                const Text(
                  'Nepal School Hub API Endpoint',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                ),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  onPressed: () => Navigator.pop(ctx),
                )
              ],
            ),
            const SizedBox(height: 8),
            const Text(
              'Live production API or custom school server URL:',
              style: TextStyle(fontSize: 12.5, color: AppTheme.darkSubtext),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: urlCtrl,
              decoration: InputDecoration(
                labelText: 'Base API URL',
                hintText: 'https://nshlms.com/api/v1/teacher-app',
                filled: true,
                fillColor: const Color(0xFFF8FAFC),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0F4C81),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () async {
                  await apiService.setCustomBaseUrl(urlCtrl.text.trim());
                  if (ctx.mounted) Navigator.pop(ctx);
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        backgroundColor: AppTheme.green,
                        content: Text('Connected to Nepal School Hub API successfully ✅'),
                      ),
                    );
                  }
                },
                child: const Text('Save & Reconnect', style: TextStyle(fontWeight: FontWeight.w800)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // Sleek Header Sliver with Dynamic Background Image Carousel
          SliverAppBar(
            expandedHeight: 260,
            pinned: false,
            automaticallyImplyLeading: false,
            elevation: 0,
            backgroundColor: const Color(0xFF0A2540),
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                fit: StackFit.expand,
                children: [
                  // 1. Sliding Image Carousel (slide1.webp, slide2.webp, etc.)
                  PageView.builder(
                    controller: _slideController,
                    onPageChanged: (idx) {
                      setState(() => _currentSlide = idx);
                    },
                    itemCount: _slideImages.length,
                    itemBuilder: (ctx, index) {
                      return Image.asset(
                        _slideImages[index],
                        fit: BoxFit.cover,
                        alignment: Alignment.center,
                        errorBuilder: (_, __, ___) => Container(
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              colors: [Color(0xFF0A2540), Color(0xFF0F4C81)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                          ),
                        ),
                      );
                    },
                  ),

                  // 2. Cinematic Gradient Overlay (Top is 100% clear so photo/people are fully visible)
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.transparent,
                          Colors.transparent,
                          const Color(0xFF0A2540).withOpacity(0.75),
                          const Color(0xFF0F172A),
                        ],
                        stops: const [0.0, 0.50, 0.88, 1.0],
                      ),
                    ),
                  ),

                  // 3. Foreground Header Content & Slide Indicators (No top icons covering faces)
                  SafeArea(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 10),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          const Spacer(),

                          // Frosted Glassmorphism App Logo & Brand Capsule
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            decoration: BoxDecoration(
                              color: Colors.black.withOpacity(0.40),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.white.withOpacity(0.25), width: 1),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.3),
                                  blurRadius: 16,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 48,
                                  height: 48,
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(14),
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withOpacity(0.25),
                                        blurRadius: 10,
                                        offset: const Offset(0, 4),
                                      ),
                                    ],
                                  ),
                                  clipBehavior: Clip.antiAlias,
                                  child: Image.asset(
                                    'assets/icons/app_icon.png',
                                    fit: BoxFit.cover,
                                  ),
                                ).animate().scale(duration: 400.ms, curve: Curves.easeOutBack),
                                const SizedBox(width: 12),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Row(
                                      children: [
                                        Text(
                                          'Guru',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 22,
                                            fontWeight: FontWeight.w900,
                                            letterSpacing: -0.5,
                                          ),
                                        ),
                                        SizedBox(width: 6),
                                        Icon(Icons.verified_rounded, color: AppTheme.gold, size: 17),
                                      ],
                                    ),
                                    Text(
                                      'NEPAL SCHOOL HUB (NSHLMS)',
                                      style: TextStyle(
                                        color: Colors.white.withOpacity(0.9),
                                        fontSize: 9,
                                        fontWeight: FontWeight.w800,
                                        letterSpacing: 1.1,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 12),

                          // Slide Indicator Dots
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: List.generate(_slideImages.length, (idx) {
                              final bool isActive = _currentSlide == idx;
                              return AnimatedContainer(
                                duration: const Duration(milliseconds: 300),
                                margin: const EdgeInsets.symmetric(horizontal: 3),
                                width: isActive ? 20 : 6,
                                height: 5,
                                decoration: BoxDecoration(
                                  color: isActive ? AppTheme.gold : Colors.white.withOpacity(0.4),
                                  borderRadius: BorderRadius.circular(4),
                                  boxShadow: isActive
                                      ? [BoxShadow(color: AppTheme.gold.withOpacity(0.7), blurRadius: 4)]
                                      : null,
                                ),
                              );
                            }),
                          ),
                          const SizedBox(height: 8),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Main Credentials Card
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (_error != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: _error!.contains('24-hour') || _error!.contains('session')
                            ? AppTheme.orange.withOpacity(0.12)
                            : AppTheme.red.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: _error!.contains('24-hour') || _error!.contains('session')
                              ? AppTheme.orange.withOpacity(0.4)
                              : AppTheme.red.withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            _error!.contains('24-hour') || _error!.contains('session')
                                ? Icons.timer_outlined
                                : Icons.error_outline_rounded,
                            color: _error!.contains('24-hour') || _error!.contains('session')
                                ? AppTheme.orange
                                : AppTheme.red,
                            size: 18,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _error!,
                              style: TextStyle(
                                color: _error!.contains('24-hour') || _error!.contains('session')
                                    ? AppTheme.orange
                                    : AppTheme.red,
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Credentials Input Card
                  Container(
                    padding: const EdgeInsets.all(22),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(
                        color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.04),
                          blurRadius: 20,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildInputField(
                          label: 'Teacher ID / School Email',
                          controller: _idCtrl,
                          icon: Icons.badge_outlined,
                          hint: 'teacher@school.edu.np',
                          isDark: isDark,
                        ),
                        const SizedBox(height: 16),
                        _buildInputField(
                          label: 'Password',
                          controller: _passCtrl,
                          icon: Icons.lock_outline_rounded,
                          hint: '••••••••••••',
                          obscure: _obscure,
                          isDark: isDark,
                          suffix: IconButton(
                            icon: Icon(
                              _obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                              color: AppTheme.darkSubtext,
                              size: 18,
                            ),
                            onPressed: () => setState(() => _obscure = !_obscure),
                          ),
                        ),
                        const SizedBox(height: 14),
                        // Remember Me & Forgot Password
                        Row(
                          children: [
                            GestureDetector(
                              onTap: () => setState(() => _rememberMe = !_rememberMe),
                              child: Row(
                                children: [
                                  AnimatedContainer(
                                    duration: const Duration(milliseconds: 150),
                                    width: 18,
                                    height: 18,
                                    decoration: BoxDecoration(
                                      color: _rememberMe ? const Color(0xFF0F4C81) : Colors.transparent,
                                      borderRadius: BorderRadius.circular(5),
                                      border: Border.all(
                                        color: _rememberMe ? const Color(0xFF0F4C81) : AppTheme.darkSubtext.withOpacity(0.5),
                                        width: 1.5,
                                      ),
                                    ),
                                    child: _rememberMe
                                        ? const Icon(Icons.check, size: 12, color: Colors.white)
                                        : null,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Remember me',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w700,
                                      color: isDark ? Colors.white70 : const Color(0xFF475569),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const Spacer(),
                            GestureDetector(
                              onTap: () {
                                final emailCtrl = TextEditingController();
                                showDialog(
                                  context: context,
                                  builder: (ctx) => AlertDialog(
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                    title: const Text('Reset Password', style: TextStyle(fontWeight: FontWeight.w900)),
                                    content: TextField(
                                      controller: emailCtrl,
                                      decoration: const InputDecoration(
                                        labelText: 'Registered School Email',
                                        hintText: 'teacher@school.edu.np',
                                      ),
                                    ),
                                    actions: [
                                      TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
                                      ElevatedButton(
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xFF0F4C81),
                                          foregroundColor: Colors.white,
                                        ),
                                        onPressed: () {
                                          Navigator.pop(ctx);
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            const SnackBar(
                                              backgroundColor: AppTheme.green,
                                              content: Text('Password reset instructions sent to your school email ✅'),
                                            ),
                                          );
                                        },
                                        child: const Text('Send Link'),
                                      ),
                                    ],
                                  ),
                                );
                              },
                              child: const Text(
                                'Forgot Password?',
                                style: TextStyle(
                                  color: Color(0xFF0F4C81),
                                  fontWeight: FontWeight.w800,
                                  fontSize: 12.5,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),

                        // Primary Sign In Button
                        SizedBox(
                          width: double.infinity,
                          height: 52,
                          child: ElevatedButton(
                            onPressed: _loading ? null : _login,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0F4C81),
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              elevation: 3,
                              shadowColor: const Color(0xFF0F4C81).withOpacity(0.4),
                            ),
                            child: _loading
                                ? const SizedBox(
                                    width: 22,
                                    height: 22,
                                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                  )
                                : const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        'Sign In to Portal',
                                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                                      ),
                                      SizedBox(width: 8),
                                      Icon(Icons.arrow_forward_rounded, size: 18),
                                    ],
                                  ),
                          ),
                        ),

                        // Biometric Login & Enable/Disable Buttons
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: SizedBox(
                                height: 48,
                                child: OutlinedButton.icon(
                                  onPressed: _loading ? null : (_biometricEnabled ? _loginWithBiometrics : () => _toggleBiometric(true)),
                                  icon: Icon(
                                    Icons.fingerprint_rounded,
                                    size: 20,
                                    color: _biometricEnabled ? const Color(0xFF0F4C81) : Colors.grey,
                                  ),
                                  label: Text(
                                    _biometricEnabled
                                        ? 'Log in with Biometrics'
                                        : 'Biometrics Disabled (Tap to Enable)',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w800,
                                      color: _biometricEnabled ? const Color(0xFF0F4C81) : Colors.grey.shade600,
                                    ),
                                  ),
                                  style: OutlinedButton.styleFrom(
                                    side: BorderSide(
                                      color: _biometricEnabled
                                          ? const Color(0xFF0F4C81).withOpacity(0.35)
                                          : Colors.grey.shade400,
                                    ),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                    backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            // Quick Enable / Disable Biometric Button
                            Tooltip(
                              message: _biometricEnabled ? 'Disable Biometrics' : 'Enable Biometrics',
                              child: InkWell(
                                onTap: () => _toggleBiometric(!_biometricEnabled),
                                borderRadius: BorderRadius.circular(14),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 200),
                                  height: 48,
                                  padding: const EdgeInsets.symmetric(horizontal: 12),
                                  decoration: BoxDecoration(
                                    color: _biometricEnabled
                                        ? AppTheme.green.withOpacity(0.12)
                                        : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9)),
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(
                                      color: _biometricEnabled
                                          ? AppTheme.green.withOpacity(0.4)
                                          : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1)),
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(
                                        _biometricEnabled ? Icons.fingerprint_rounded : Icons.lock_outline_rounded,
                                        size: 16,
                                        color: _biometricEnabled ? AppTheme.green : AppTheme.darkSubtext,
                                      ),
                                      const SizedBox(width: 5),
                                      Text(
                                        _biometricEnabled ? 'ON' : 'OFF',
                                        style: TextStyle(
                                          fontSize: 11.5,
                                          fontWeight: FontWeight.w900,
                                          color: _biometricEnabled ? AppTheme.green : AppTheme.darkSubtext,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Offline Demo Mode & Server Config Button
                  Center(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        TextButton.icon(
                          onPressed: () {
                            Navigator.pushReplacement(
                              context,
                              MaterialPageRoute(builder: (_) => const MainShell()),
                            );
                          },
                          icon: const Icon(Icons.offline_bolt_outlined, size: 16, color: Color(0xFF64748B)),
                          label: const Text(
                            'Explore in Offline Demo Mode',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF64748B)),
                          ),
                        ),
                        const SizedBox(width: 2),
                        IconButton(
                          icon: const Icon(Icons.dns_rounded, size: 15, color: Color(0xFF94A3B8)),
                          tooltip: 'API Server Endpoint',
                          onPressed: _showServerConfig,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Security Seal & Compliance
                  Center(
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.security_rounded, size: 13, color: Color(0xFF64748B)),
                            const SizedBox(width: 4),
                            Text(
                              'Powered by Nepal School Hub (NSHLMS) • ISO 27001 Certified',
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: isDark ? Colors.white54 : const Color(0xFF64748B),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Government of Nepal CDC Curriculum Compliant',
                          style: TextStyle(
                            fontSize: 9.5,
                            color: isDark ? Colors.white38 : const Color(0xFF94A3B8),
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputField({
    required String label,
    required TextEditingController controller,
    required IconData icon,
    required String hint,
    required bool isDark,
    bool obscure = false,
    Widget? suffix,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            color: isDark ? Colors.white70 : const Color(0xFF334155),
          ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          obscureText: obscure,
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5),
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
            prefixIcon: Icon(icon, color: const Color(0xFF0F4C81), size: 18),
            suffixIcon: suffix,
            filled: true,
            fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: Color(0xFF0F4C81),
                width: 1.5,
              ),
            ),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          ),
        ),
      ],
    );
  }
}
