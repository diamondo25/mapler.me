using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using MySql.Data.MySqlClient;
using System.Diagnostics;

namespace MPLRServer
{
    public class MySQL_Connection : IDisposable
    {

        public struct NowType { }
        public struct UnescapedValue { public object Value; }

        public static NowType NOW = new NowType();

        public MySqlDataReader Reader { get; private set; }
        public bool Stop { get; set; }
        public int QueryCounter { get; private set; }
        public int QueryCounterSinceLastReset { get; private set; }

        private MySqlConnection _connection;
        private MySqlCommand _command;
        private string _connectionString;
        private Stack<KeyValuePair<string, string>> _queryList = new Stack<KeyValuePair<string, string>>();

        public static MySQL_Connection Instance { get; private set; }
        public static void Initialize()
        {
            string ip = "127.0.0.1";
            if (System.IO.File.Exists("local")) ip = "mc.craftnet.nl";

            Instance = new MySQL_Connection("maplestats", "maplederp", "maplestats", ip);
        }

        ~MySQL_Connection()
        {
            Dispose(false);
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }

        protected virtual void Dispose(bool disposing)
        {
            Stop = true;
            if (_connection != null)
            {
                _connection.Close();
                _connection.Dispose();
                _connection = null;
            }
            if (_command != null)
            {
                _command.Dispose();
                _connection = null;
            }
            if (Reader != null)
            {
                Reader.Dispose();
                Reader = null;
            }
            _queryList.Clear();
        }

        public MySQL_Connection(string pUsername, string pPassword, string pDatabase, string pHost, ushort pPort = 3306)
        {
            QueryCounter = 0;

            Stop = false;
            _connectionString = "Server=" + pHost + "; Port=" + pPort + "; Database=" + pDatabase + "; Uid=" + pUsername + "; Pwd=" + pPassword;
            Connect();
        }

        private void AddQuery(string pQuery)
        {
            if (_queryList.Count > 10) _queryList.Pop();
            _queryList.Push(new KeyValuePair<string, string>(pQuery, new StackTrace().ToString()));
        }

        public List<KeyValuePair<string, string>> GetRanQueries()
        {
            return _queryList.ToList();
        }

        public void Connect()
        {
            try
            {
                _connection = new MySqlConnection(_connectionString);
                _connection.StateChange += new System.Data.StateChangeEventHandler(connection_StateChange);
                _connection.Open();
                QueryCounterSinceLastReset = 0;
            }
            catch (Exception ex)
            {
                string line = string.Format("Got exception at MySQL_Connection.Connect():\r\n {0}", ex.ToString());

                Logger.WriteLine(ex.ToString());
                throw new Exception(line);
            }
        }

        void connection_StateChange(object sender, System.Data.StateChangeEventArgs e)
        {
            if (Stop) return;

            if (e.OriginalState != System.Data.ConnectionState.Connecting && e.CurrentState == System.Data.ConnectionState.Closed)
            {
                Logger.WriteLine("MySQL connection closed. Reconnecting!");
                _connection.StateChange -= connection_StateChange;
                Connect();
            }
            else if (e.CurrentState == System.Data.ConnectionState.Open)
            {
                Logger.WriteLine("MySQL connection opened!");
                Logger.WriteLine("MySQL Server: {0}", _connection.ServerVersion);
            }
        }

        public object RunQuery(string pQuery)
        {
            try
            {
                if (Reader != null && !Reader.IsClosed)
                {
                    Reader.Close();
                    Reader.Dispose();
                    Reader = null;
                }

                if (!_connection.Ping())
                {
                    Logger.WriteLine("Lost connection to DB... Trying to reconnect");
                    // Close and reopen
                    _connection.Close();
                    _connection.Dispose();
                    _connection = null;

                    Connect();
                    Logger.WriteLine("Done: {0}", _connection.Ping());
                }


                _command = new MySqlCommand(pQuery, _connection);
                AddQuery(pQuery);
                QueryCounter++;
                QueryCounterSinceLastReset++;
                if (pQuery.StartsWith("SELECT"))
                {
                    Reader = _command.ExecuteReader();
                    return Reader;
                }
                else if (pQuery.StartsWith("DELETE") || pQuery.StartsWith("UPDATE") || pQuery.StartsWith("INSERT"))
                    return _command.ExecuteNonQuery();

            }
            catch (InvalidOperationException)
            {
                Logger.WriteLine("Lost connection to DB... Trying to reconnect and wait a second before retrying to run query.");
                Connect();
                System.Threading.Thread.Sleep(1000);
                return RunQuery(pQuery);
            }
            catch (MySqlException ex)
            {
                Logger.WriteLine("ERROR CODE: {0}", ex.Number);
                if (ex.Number == 2055 || ex.Number == 0)
                {
                    Logger.WriteLine("Lost connection to DB... Trying to reconnect and wait a second before retrying to run query.");
                    Connect();
                    return RunQuery(pQuery);
                }
                else
                {
                    Logger.WriteLine(ex.ToString());
                    Logger.WriteLine(pQuery);
                    throw new Exception(string.Format("Got exception @ MySQL_Connection::RunQuery({0}) : {1}", pQuery, ex.ToString()));
                }
            }
            return 0;
        }

        public int GetLastInsertId()
        {
            return (int)_command.LastInsertedId;
        }


        public bool Ping()
        {
            if (Reader != null && !Reader.IsClosed)
                return false;
            return _connection.Ping();
        }


        public static string QueryQuery(params object[] pParams)
        {
            string result = "(";
            foreach (object par in pParams)
            {
                result += Escape(par) + ",";
            }

            result = result.Remove(result.Length - 1) + ")";
            return result;
        }

        public static string Escape(object pValue)
        {
            if (pValue == null)
            {
                return "NULL";
            }
            else if (pValue.GetType() == typeof(UnescapedValue))
            {
                return ((UnescapedValue)pValue).Value.ToString();
            }
            else if (pValue.GetType() == typeof(string))
            {
                return "'" + MySqlHelper.EscapeString((string)pValue) + "'";
            }
            else if (pValue.GetType() == typeof(NowType))
            {
                return "NOW()";
            }
            else if (pValue.GetType() == typeof(DateTime))
            {
                return "'" + ((DateTime)pValue).ToString("yyyy-MM-dd HH:mm:ss") + "'";
            }
            else
            {
                return pValue.ToString();
            }
        }

        public static string QueryUpdate(params string[] pParams)
        {
            string result = "";
            foreach (string par in pParams)
            {
                result += string.Format("`{0}` = VALUES(`{0}`),", par);
            }
            return result.TrimEnd(',');
        }
    }
}