-- $CVSHeader: celibero/core/db/adodb/session/adodb-sessions.oracle.clob.sql,v 1.1.1.1 2005/02/11 13:20:40 tom Exp $

DROP TABLE adodb_sessions;

CREATE TABLE sessions (
	sesskey		CHAR(32)	DEFAULT '' NOT NULL,
	expiry		INT		DEFAULT 0 NOT NULL,
	expireref	VARCHAR(64)	DEFAULT '',
	data		CLOB		DEFAULT '',
	PRIMARY KEY	(sesskey)
);

CREATE INDEX ix_expiry ON sessions (expiry);

QUIT;
