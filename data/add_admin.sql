INSERT INTO user_has_privilege(user_id, privilege) SELECT id, "admin" FROM user WHERE login="test";
INSERT INTO token(user_id, token, valid_until, type)  SELECT id, "token", "2999-01-01 00:00:00", "login" FROM user WHERE login="test";
