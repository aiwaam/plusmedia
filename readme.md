<h1>Plus Media Test App</h1>

## This App is for the Plus Media's Test App to post a message into the Facebook.

You may need to create a DB Table

CREATE TABLE `fb_post` (
  `post_id` varchar(50) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `response` text,
  `issued` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`)
)

Please contact me randy.akj@gmail.com
