ALTER TABLE fee_payments ADD COLUMN expected_pay integer;
UPDATE fee_payments SET expected_pay = paid_amount;
