# Test Scenarios

## Scenario 1

Customer abandons checkout.

Expected:

- Order cancelled after timeout
- Booking rejected

## Scenario 2

Customer pays deposit.

Expected:

- Order not cancelled
- Booking approved

## Scenario 3

Dry-run enabled.

Expected:

- Orders logged
- No cancellation