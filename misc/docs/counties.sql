
set names "utf8";

drop table if exists region;
CREATE TABLE `region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(45) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `local_name` varchar(255) DEFAULT NULL,
  `seat_city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `juristidction` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into region values(null,'IE','Antrim','Aontroim','Antrim','Ulster','Northern Ireland');
insert into region values(null,'IE','Armagh','Ard Mhacha','Armagh','Ulster','Northern Ireland');
insert into region values(null,'IE','Carlow','Ceatharlach','Carlow','Leinster','Ireland');
insert into region values(null,'IE','Cavan','An Cabhán','Cavan','Ulster','Ireland');
insert into region values(null,'IE','Clare','An Clár','Ennis','Munster','Ireland');
insert into region values(null,'IE','Cork','Corcaigh','Cork','Munster','Ireland');
insert into region values(null,'IE','Donegal','Dún na nGall','Lifford','Ulster','Ireland');
insert into region values(null,'IE','Down','An Dún','Downpatrick','Ulster','Northern Ireland');
insert into region values(null,'IE','Dublin','Áth Cliath','Dublin','Leinster','Ireland');
insert into region values(null,'IE','Fermanagh','Fear Manach','Enniskillen','Ulster','Northern Ireland');
insert into region values(null,'IE','Galway','Gaillimh','Galway','Connacht','Ireland');
insert into region values(null,'IE','Kerry','Ciarraí','Tralee','Munster','Ireland');
insert into region values(null,'IE','Kildare','Cill Dara','Naas','Leinster','Ireland');
insert into region values(null,'IE','Kilkenny','Cill Chainnigh','Kilkenny','Leinster','Ireland');
insert into region values(null,'IE','Laois','Laois','Portlaoise','Leinster','Ireland');
insert into region values(null,'IE','Leitrim','Liatroim','Carrick-on-Shannon','Connacht','Ireland');
insert into region values(null,'IE','Limerick','Luimneach','Limerick','Munster','Ireland');
insert into region values(null,'IE','Londonderry','Doire','Coleraine','Ulster','Northern Ireland');
insert into region values(null,'IE','Longford','An Longfort','Longford','Leinster','Ireland');
insert into region values(null,'IE','Louth','Lú','Dundalk','Leinster','Ireland');
insert into region values(null,'IE','Mayo','Maigh Eo','Castlebar','Connacht','Ireland');
insert into region values(null,'IE','Meath','An Mhí','Navan','Leinster','Ireland');
insert into region values(null,'IE','Monaghan','Muineachán','Monaghan','Ulster','Ireland');
insert into region values(null,'IE','Offaly','Uíbh Fhailí','Tullamore','Leinster','Ireland');
insert into region values(null,'IE','Roscommon','Ros Comáin','Roscommon','Connacht','Ireland');
insert into region values(null,'IE','Sligo','Sligeach','Sligo','Connacht','Ireland');
insert into region values(null,'IE','Tipperary','Tiobraid Árann','Clonmel & Nenagh','Munster','Ireland');
insert into region values(null,'IE','Tyrone','Tír Eoghain','Omagh','Ulster','Northern Ireland');
insert into region values(null,'IE','Waterford','Port Láirge','Dungarvan','Munster','Ireland');
insert into region values(null,'IE','Westmeath','An Iarmhí','Mullingar','Leinster','Ireland');
insert into region values(null,'IE','Wexford','Loch Garman','Wexford','Leinster','Ireland');
insert into region values(null,'IE','Wicklow','Cill Mhantáin','Wicklow','Leinster','Ireland');
