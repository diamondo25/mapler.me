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
        public bool IgnoreDuplicates { get; set; }
        private List<string> _rows = new List<string>();
        private Dictionary<string, List<object>> _rowsColumned = new Dictionary<string, List<object>>();
        private int _rowsColumnedCount = 0;

        public int RowCount { get { return _rows.Count; } }
        public bool HasColumns { get { return _columns.Count > 0; } } // For initialization check

        public InsertQueryBuilder(string pTableName)
        {
            TableName = pTableName;
            OnDuplicateUpdate = false;
            IgnoreDuplicates = false;
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
            if (_rowsColumned != null)
            {
                _rowsColumned.Clear();
                _rowsColumned = null;
            }
        }

        public void AddColumn(string pName, bool pInUpdate = false)
        {
            _columns.Add(new Column() { Name = pName, InUpdate = pInUpdate });
        }

        public void AddColumnWithValue(string pName, object pValue, bool pInUpdate = true)
        {
            var dunno = _columns.Count(a => { return a.Name == pName; });
            if (dunno == 0)
            {
                _columns.Add(new Column() { Name = pName, InUpdate = pInUpdate });
                _rowsColumned.Add(pName, new List<object>());
            }
            _rowsColumned[pName].Add(pValue);
            if (_rowsColumnedCount < _rowsColumned[pName].Count)
                _rowsColumnedCount = _rowsColumned[pName].Count;
        }

        public void AddColumns(bool pInUpdate, params string[] pColumnNames)
        {
            foreach (var name in pColumnNames)
                _columns.Add(new Column() { Name = name, InUpdate = pInUpdate });
        }

        public void AddRow(params object[] pColumns)
        {
            if (pColumns.Length != _columns.Count) throw new Exception("Incorrect column count!");
            _rows.Add(MySQL_Connection.BuildValuesRow(pColumns));
        }

        public bool HasRows() { return _rows.Count > 0 || _rowsColumned.Count > 0; }

        public override string ToString()
        {
            if (!HasRows()) throw new Exception("Row count = 0");

            if (IgnoreDuplicates && OnDuplicateUpdate) throw new Exception("Can't use both IgnoreDuplicates and OnDuplicateUpdate");

            StringBuilder sb = new StringBuilder();
            sb.Append("INSERT " + (IgnoreDuplicates ? "IGNORE " : "") + "INTO\r\n\t`" + this.TableName + "`\r\n\t(");
            string[] columnlist = new string[_columns.Count];
            for (int i = 0; i < _columns.Count; i++)
                columnlist[i] = "`" + _columns[i].Name + "`";

            sb.Append(string.Join(", ", columnlist));
            sb.Append(")\r\nVALUES\r\n\t");

            List<string> tmp = new List<string>(_rows);
            if (_rowsColumnedCount > 0)
            {
                for (int j = 0; j < _columns.Count; j++)
                {
                    if (_rowsColumned[_columns[j].Name].Count != _rowsColumnedCount)
                    {
                        throw new Exception("Column " + _columns[j].Name + " did not have enough values!!!");
                    }
                }
                for (int i = 0; i < _rowsColumnedCount; i++)
                {
                    object[] inputobjects = new object[_columns.Count];
                    for (int j = 0; j < _columns.Count; j++)
                    {
                        inputobjects[j] = _rowsColumned[_columns[j].Name][i];
                    }
                    tmp.Add(MySQL_Connection.BuildValuesRow(inputobjects));
                }
            }

            sb.Append(string.Join(",\r\n\t", tmp));

            if (OnDuplicateUpdate)
            {
                List<string> updatecolumns = new List<string>();
                foreach (Column col in _columns.Where(c => { return c.InUpdate; }))
                    updatecolumns.Add(string.Format("`{0}` = VALUES(`{0}`)", col.Name));

                if (updatecolumns.Count > 0)
                {
                    sb.Append("\r\nON DUPLICATE KEY UPDATE\r\n\t");
                    sb.Append(string.Join(",\r\n\t", updatecolumns.ToArray()));
                }
            }
            sb.Append(";");

            return sb.ToString();
        }

        public void RunQuery(string pSaveFile = null)
        {
            if (RowCount > 0)
            {
                string q = ToString();
                if (pSaveFile != null)
                    System.IO.File.WriteAllText(pSaveFile, q);
                MySQL_Connection.Instance.RunQuery(q);
            }
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

            StringBuilder sb = new StringBuilder();
            sb.Append("UPDATE `" + TableName + "` SET ");
            string tmp = "";
            foreach (var kvp in _columns)
                tmp += string.Format("`{0}` = {1},", kvp.Key, kvp.Value);

           
            sb.Append(tmp.TrimEnd(','));
            if (_whereColumns.Count > 0)
            {
                sb.Append(" WHERE ");
                tmp = "";
                foreach (var kvp in _whereColumns)
                    tmp += string.Format(" `{0}` = {1} AND", kvp.Key, kvp.Value);
                sb.Append(tmp.Substring(0, tmp.Length - 4));
            }

            return sb.ToString();
        }

        public void RunQuery(string pSaveFile = null)
        {
            string q = ToString();
            if (pSaveFile != null)
                System.IO.File.WriteAllText(pSaveFile, q);
            MySQL_Connection.Instance.RunQuery(q);
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

        public void RunQuery(string pSaveFile = null)
        {
            string q = ToString();
            if (pSaveFile != null)
                System.IO.File.WriteAllText(pSaveFile, q);
            MySQL_Connection.Instance.RunQuery(q);
        }
    }
}
