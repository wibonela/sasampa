class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final bool hasPin;
  final Company? company;
  final Branch? currentBranch;
  final List<String> permissions;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.hasPin,
    this.company,
    this.currentBranch,
    this.permissions = const [],
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'],
      hasPin: json['has_pin'] ?? false,
      company: json['company'] != null ? Company.fromJson(json['company']) : null,
      currentBranch: json['current_branch'] != null ? Branch.fromJson(json['current_branch']) : null,
      permissions: json['permissions'] != null
          ? List<String>.from(json['permissions'])
          : [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'has_pin': hasPin,
      'company': company?.toJson(),
      'current_branch': currentBranch?.toJson(),
      'permissions': permissions,
    };
  }

  bool get isPlatformAdmin => role == 'platform_admin';
  bool get isCompanyOwner => role == 'company_owner';
  bool get isCashier => role == 'cashier';

  bool hasPermission(String permission) {
    if (isCompanyOwner || isPlatformAdmin) return true;
    return permissions.contains(permission) || permissions.contains('*');
  }
}

class Company {
  final int id;
  final String name;
  final String? logo;
  final String status;
  final bool branchesEnabled;

  Company({
    required this.id,
    required this.name,
    this.logo,
    required this.status,
    required this.branchesEnabled,
  });

  factory Company.fromJson(Map<String, dynamic> json) {
    return Company(
      id: json['id'],
      name: json['name'],
      logo: json['logo'],
      status: json['status'],
      branchesEnabled: json['branches_enabled'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'logo': logo,
      'status': status,
      'branches_enabled': branchesEnabled,
    };
  }

  bool get isApproved => status == 'approved';
}

class Branch {
  final int id;
  final String name;
  final String code;

  Branch({
    required this.id,
    required this.name,
    required this.code,
  });

  factory Branch.fromJson(Map<String, dynamic> json) {
    return Branch(
      id: json['id'],
      name: json['name'],
      code: json['code'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'code': code,
    };
  }
}

class MobileAccess {
  final String? status;
  final bool canUseMobile;
  final String? requestedAt;
  final String? approvedAt;
  final String? rejectionReason;
  final String? revocationReason;

  MobileAccess({
    this.status,
    required this.canUseMobile,
    this.requestedAt,
    this.approvedAt,
    this.rejectionReason,
    this.revocationReason,
  });

  factory MobileAccess.fromJson(Map<String, dynamic> json) {
    return MobileAccess(
      status: json['status'],
      canUseMobile: json['can_use_mobile'] ?? false,
      requestedAt: json['requested_at'],
      approvedAt: json['approved_at'],
      rejectionReason: json['rejection_reason'],
      revocationReason: json['revocation_reason'],
    );
  }

  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';
  bool get isRevoked => status == 'revoked';
  bool get needsRequest => status == null;
}
