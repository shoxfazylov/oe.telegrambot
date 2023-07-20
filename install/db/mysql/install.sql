create table if not exists b_oe_tgbot_keyboard
(
	ID int not null auto_increment,
	USER_ID varchar(255) NOT NULL,
	KEYBOARD text not null,
	TIMESTAMP_CHANGE timestamp,
	primary key (ID)
);
create table if not exists b_oe_tgbot_wishlist(
	ID int not null auto_increment,
	USER_ID varchar(255) NOT NULL,
	PRODUCT_ID int not null,
	TIMESTAMP_CHANGE timestamp,
	primary key (ID)
);
create table if not exists b_oe_tgbot_order(
	ID int not null auto_increment,
	USER_ID int NOT NULL,
	ORDER_ID int NOT NULL,
	PAYSYSTEM_ID int NOT NULL,
	DELIVERY_ID int NOT NULL,
	PHONE varchar(255) NOT NULL,
	LOCATION varchar(255) NOT NULL,
	PRODUCTS varchar(255) NOT NULL,
	TIMESTAMP_CHANGE timestamp,
	primary key (ID)
);
create table if not exists b_oe_tgbot_posting(
  ID int not null auto_increment,
  TITLE VARCHAR(255) NOT NULL,
  USER_ID INT(10) UNSIGNED NOT NULL,
  DATE_INSERT DATETIME NOT NULL,
  DATE_SEND DATETIME NULL,
  COUNT_SEND_ALL INT(11) DEFAULT 0 NOT NULL,
  STATUS CHAR(1) DEFAULT 'D' NOT NULL,
  TEXT LONGTEXT NOT NULL,
  FILE_ID INT(18) NULL,
  PRIMARY KEY (ID)
);
create table if not exists b_oe_tgbot_posting_log(
  ID int not null auto_increment,
  POST_ID int NOT NULL,
  USER_ID int NOT NULL,
  CHAT_ID int NOT NULL,
  STATUS CHAR(1) DEFAULT 'D' NOT NULL,
  PRIMARY KEY (ID)
);

