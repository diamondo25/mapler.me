using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class InsertQueryBuilder : IDisposable
    {
        struct Column
        {
            public string Name { get; set; }
            public bool InUpdate { get; set; }
        }

        private List<Column> _columns = new List<Column>();
        public string TableName { get; private set; }
        public bool OnDuplicateUpdate { get; set; }
        private List<string> _rows = new List<string>();

        public int RowCount { get { return _rows.Count; } }

        public InsertQueryBuilder(string pTableName)
        {
            TableName = pTableName;
        }

        ~InsertQueryBuilder()
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
            if (_columns != null)
            {
                _columns.Clear();
                _columns = null;
            }
            if (_rows != null)
            {
                _rows.Clear();
                _rows = null;
            }
        }

        public void AddColumn(string pName, bool pInUpdate = false)
        {
            _columns.Add(new Column() { Name = pName, InUpdate = pInUpdate });
        }

        public void AddColumns(bool pInUpdate, params string[] pColumnNames)
        {
            foreach (var name in pColumnNames)
            {
                _columns.Add(new Column() { Name = name, InUpdate = pInUpdate });
            }
        }

        public void AddRow(params object[] pColumns)
        {
            if (pColumns.Length != _columns.Count) throw new Exception("Incorrect column count!");
            _rows.Add(MySQL_Connection.QueryQuery(pColumns));
        }

        public bool HasRows() { return _rows.Count > 0; }

        public override string ToString()
        {
            if (!HasRows()) throw new Exception("Row count = 0");

            string query = "INSERT INTO\r\n\t`" + this.TableName + "`\r\n\t(";
            string[] columnlist = new string[_columns.Count];
            for (int i = 0; i < _columns.Count; i++)
                columnlist[i] = "`" + _columns[i].Name + "`";

            query += string.Join(", ", columnlist);
            query += ")\r\nVALUES\r\n\t";

            query += string.Join(",\r\n\t", _rows);

            List<string> updatecolumns = new List<string>();
            foreach (Column col in _columns.Where(c => { return c.InUpdate; }))
            {
                updatecolumns.Add(string.Format("`{0}` = VALUES(`{0}`)", col.Name));
            }

            if (updatecolumns.Count > 0)
            {
                query += "\r\nON DUPLICATE KEY UPDATE\r\n\t";
                query += string.Join(",\r\n\t", updatecolumns.ToArray());
            }
            query += ";";

            return query;
        }
    }

    class UpdateQueryBuilder : IDisposable
    {
        private Dictionary<string, string> _columns = new Dictionary<string, string>();
        private Dictionary<string, string> _whereColumns = new Dictionary<string, string>();
        public string TableName { get; private set; }

        public UpdateQueryBuilder(string pTableName)
        {
            TableName = pTableName;
        }

        ~UpdateQueryBuilder()
        {
            Dispose(false);
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }

        protected void Dispose(bool disposing)
        {
            if (_columns != null)
            {
                _columns.Clear();
                _columns = null;
            }
            if (_whereColumns != null)
            {
                _whereColumns.Clear();
                _whereColumns = null;
            }
        }

        public void SetColumn(string pKey, object pValue)
        {
            _columns.Add(pKey, MySQL_Connection.Escape(pValue));
        }

        public void SetWhereColumn(string pKey, object pValue)
        {
            _whereColumns.Add(pKey, MySQL_Connection.Escape(pValue));
        }

        public override string ToString()
        {
            if (_columns.Count == 0) throw new Exception("No columns set");

            string query = "UPDATE `" + TableName + "` SET ";
            foreach (var kvp in _columns)
                query += string.Format("`{0}` = {1},", kvp.Key, kvp.Value);

            query = query.TrimEnd(',');
            if (_whereColumns.Count > 0)
            {
                query += " WHERE ";
                foreach (var kvp in _whereColumns)
                    query += string.Format(" `{0}` = {1} AND", kvp.Key, kvp.Value);

                query = query.Substring(0, query.Length - 4);
            }

            return query;
        }
    }

    class DeleteQueryBuilder : IDisposable
    {
        private Dictionary<string, string> _whereColumns = new Dictionary<string, string>();
        public string TableName { get; private set; }

        public DeleteQueryBuilder(string pTableName)
        {
            TableName = pTableName;
        }

        ~DeleteQueryBuilder()
        {
            Dispose(false);
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }

        protected void Dispose(bool disposing)
        {
            if (_whereColumns != null)
            {
                _whereColumns.Clear();
                _whereColumns = null;
            }
        }

        public void SetWhereColumn(string pKey, object pValue)
        {
            _whereColumns.Add(pKey, MySQL_Connection.Escape(pValue));
        }

        public override string ToString()
        {
            if (_whereColumns.Count == 0) throw new Exception("No columns set");

            string query = "DELETE FROM `" + TableName + "` WHERE ";
            foreach (var kvp in _whereColumns)
                query += string.Format(" `{0}` = {1} AND", kvp.Key, kvp.Value);

            query = query.Substring(0, query.Length - 4);

            return query;
        }
    }
}
