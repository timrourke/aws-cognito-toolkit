provider "aws" {
  region = "us-east-1"
}

resource "aws_cognito_user_pool" "incognito_test_user_pool" {
  name = "incognito_test_user_pool"

  admin_create_user_config {
    allow_admin_create_user_only = false
  }

  password_policy {
    minimum_length                   = 8
    require_lowercase                = true
    require_numbers                  = true
    require_symbols                  = true
    require_uppercase                = true
    temporary_password_validity_days = 1
  }
}

resource "aws_cognito_user_pool_client" "incognito_test_client" {
  name = "incognito_test_client"

  user_pool_id = aws_cognito_user_pool.incognito_test_user_pool.id

  generate_secret     = true
  explicit_auth_flows = ["ADMIN_NO_SRP_AUTH"]

  depends_on = [aws_cognito_user_pool.incognito_test_user_pool]
}

output "aws_cognito_user_pool_client_id" {
  value = aws_cognito_user_pool_client.incognito_test_client.id
}

output "aws_cognito_user_pool_client_secret" {
  value = aws_cognito_user_pool_client.incognito_test_client.client_secret
}

output "aws_cognito_user_pool_id" {
  value = aws_cognito_user_pool.incognito_test_user_pool.id
}
