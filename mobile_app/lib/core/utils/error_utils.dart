import 'package:dio/dio.dart';

String extractErrorMessage(dynamic error, String fallback) {
  if (error is DioException) {
    final response = error.response;
    if (response != null && response.data is Map<String, dynamic>) {
      final data = response.data as Map<String, dynamic>;
      // Laravel validation errors: { "message": "...", "errors": { "field": ["..."] } }
      if (data.containsKey('errors') && data['errors'] is Map) {
        final errors = data['errors'] as Map;
        final firstError = errors.values.first;
        if (firstError is List && firstError.isNotEmpty) {
          return firstError.first.toString();
        }
      }
      // Simple message: { "message": "..." }
      if (data.containsKey('message') && data['message'] is String) {
        return data['message'] as String;
      }
    }

    final statusCode = response?.statusCode;
    if (statusCode == 422) return 'Validation error. Please check your input.';
    if (statusCode == 401) return 'Session expired. Please login again.';
    if (statusCode == 403) return 'Access denied.';
    if (statusCode == 404) return 'Resource not found.';
    if (statusCode == 500) return 'Server error. Please try again later.';

    if (error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.connectionTimeout) {
      return 'No internet connection. Please check your network.';
    }
    if (error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout) {
      return 'Connection timed out. Please try again.';
    }
  }
  return fallback;
}
