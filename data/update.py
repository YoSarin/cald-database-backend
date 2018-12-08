#! /usr/bin/python
# yeah, i know, it's ugly. I have no time to do it nice way... :(
import argparse
import getpass
import mysql.connector
import os, sys, traceback

def main():
    parser = argparse.ArgumentParser(description="Script will perform updates/rollbacks on DB")
    parser.add_argument('--username', default="cald", type=str, help="Username for DB access")
    parser.add_argument('--dbname', default="cald", type=str, help="Username for DB access")
    parser.add_argument('--host', default="127.0.0.1", type=str, help="Server to access DB at")
    parser.add_argument('--port', default=3306, type=int, help="Port to access DB at")
    parser.add_argument('--password', type=str, help="Password for DB (not reccomended to use, you will be prompted for hidden input if omited)")
    parser.add_argument('--rollback', action='store_true', help="if set, then it won't update DB, but it will rollback one version down")

    args = parser.parse_args()

    password = args.password
    if not password:
        password = getpass.getpass(prompt=("Password for user %s: " % args.username))

    db = mysql.connector.connect(
        host     = args.host,
        user     = args.username,
        password = password,
        database = args.dbname,
        port     = args.port
    )

    version = -1
    try:
        cur = db.cursor()
        cur.execute("SELECT db_version FROM db_metadata LIMIT 1")
        version = cur.fetchone()[0]
        cur.close()
    except mysql.connector.Error as e:
        pass  # table does not exist yet. Thats okay.

    print "Current DB version: %s" % version

    files_to_perform = []
    basedir = os.path.join(os.path.dirname(__file__), "migrations")
    if args.rollback:
        files = sorted(os.listdir(os.path.join(basedir, "rollback")))
        for fileName in files:
            id, name = fileName.split("_", 1)
            if int(id) == version:
                files_to_perform.append(os.path.join(basedir, "rollback", fileName))
                break
    else:
        files = sorted(os.listdir(os.path.join(basedir, "deploy")))
        for fileName in files:
            id, name = fileName.split("_", 1)
            if int(id) > version:
                files_to_perform.append(os.path.join(basedir, "deploy", fileName))

    if len(files_to_perform) == 0:
        print "Nothing to do, we're okay!"

    try:
        for fileName in files_to_perform:
            with open(fileName, "r") as f:
                file_version = int(os.path.basename(fileName).split("_")[0])
                if args.rollback:
                    file_version -= 1

                data = f.readlines()
                if file_version >= 0:
                    data.append("UPDATE db_metadata SET db_version = %s, changed_at = NOW();\n" % file_version)
                print "Performing: %s\n\t%s" % (os.path.basename(fileName), "\t".join(data))

                if not db.in_transaction:
                    db.start_transaction()
                cur = db.cursor()
                for result in cur.execute("".join(data), multi=True):
                    print result
                verify(cur)
                db.commit()
                cur.close()

    except mysql.connector.Error as e:
        traceback.print_exc(file=sys.stdout)
        print "Error code:", e.errno         # error number
        print "SQLSTATE value:", e.sqlstate  # SQLSTATE value
        print "Error message:", e.msg        # error message
        print "Error:", e                    # errno, sqlstate, msg values
        s = str(e)
        print "Error:", s                    # errno, sqlstate, msg values


        print "Stopping execution"
        print "rolling back (any created tables stays there :()"
        db.rollback()

    db.close()

def verify(cur):
    try:
        cur.fetchall()
    except mysql.connector.errors.InterfaceError as ie:
        if ie.msg == 'No result set to fetch from.':
            print "nothing to fetch, no other error"
            pass
        else:
            raise

if __name__ == "__main__":
    main()
