alter table `billing_paymentmethod` add column exp_month char(2);
alter table `billing_paymentmethod` add column exp_year char(2);
update `billing_paymentmethod` set exp_month=left(cc_expiry,2);
update `billing_paymentmethod` set exp_year=right(cc_expiry,2);
alter table `billing_paymentmethod` drop cc_expiry;
