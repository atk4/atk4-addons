alter table `billing_paymentmethod` add column user_id int; 
alter table `billing_paymentmethod` add key(user_id);
