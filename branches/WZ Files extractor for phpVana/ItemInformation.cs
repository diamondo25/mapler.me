using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace WZ_Files_extractor_for_phpVana
{
    class ItemInformation
    {
        enum ColumnTypes
        {
            String,
            Int,
            Short,
            Byte,
        }


        public static ItemInformation Instance { get; private set; }
        public static void Init()
        {
            Instance = new ItemInformation();
        }

        public ItemInformation()
        {
            _acceptedColumns.Add("itemid", ColumnTypes.Int);

            _acceptedColumns.Add("reqjob", ColumnTypes.Short);
            _columnFails.Add("reqjob2", "reqspecjob");
            _acceptedColumns.Add("reqspecjob", ColumnTypes.Short);
            _acceptedColumns.Add("reqrace", ColumnTypes.Short);
            _acceptedColumns.Add("reqlevel", ColumnTypes.Short);
            _acceptedColumns.Add("reqstr", ColumnTypes.Short);
            _acceptedColumns.Add("reqdex", ColumnTypes.Short);
            _acceptedColumns.Add("reqint", ColumnTypes.Short);
            _acceptedColumns.Add("reqluk", ColumnTypes.Short);
            _acceptedColumns.Add("reqpop", ColumnTypes.Short);

            _acceptedColumns.Add("incstr", ColumnTypes.Short);
            _acceptedColumns.Add("incdex", ColumnTypes.Short);
            _acceptedColumns.Add("incint", ColumnTypes.Short);
            _acceptedColumns.Add("incluk", ColumnTypes.Short);
            _acceptedColumns.Add("incmhp", ColumnTypes.Short);
            _acceptedColumns.Add("incmmp", ColumnTypes.Short);
            _acceptedColumns.Add("incpdd", ColumnTypes.Short);
            _acceptedColumns.Add("incmdd", ColumnTypes.Short);
            _acceptedColumns.Add("incpad", ColumnTypes.Short);
            _acceptedColumns.Add("incmad", ColumnTypes.Short);
            _acceptedColumns.Add("inceva", ColumnTypes.Short);
            _acceptedColumns.Add("incacc", ColumnTypes.Short);
            _acceptedColumns.Add("incpvpdamage", ColumnTypes.Short);
            _acceptedColumns.Add("incspeed", ColumnTypes.Short);
            _acceptedColumns.Add("incjump", ColumnTypes.Short);
            _acceptedColumns.Add("inccraft", ColumnTypes.Short);
            _acceptedColumns.Add("incmhpr", ColumnTypes.Short);
            _acceptedColumns.Add("incmmpr", ColumnTypes.Short);
            _acceptedColumns.Add("inchp", ColumnTypes.Short);
            _acceptedColumns.Add("incrmaf", ColumnTypes.Short);
            _acceptedColumns.Add("incrmas", ColumnTypes.Short);
            _acceptedColumns.Add("incrmai", ColumnTypes.Short);
            _acceptedColumns.Add("incrmal", ColumnTypes.Short);
            _acceptedColumns.Add("incswim", ColumnTypes.Short);
            _acceptedColumns.Add("incfatigue", ColumnTypes.Short);

            _acceptedColumns.Add("bdr", ColumnTypes.Short);
            _columnFails.Add("recoveryhp", "hprecovery");
            _acceptedColumns.Add("hprecovery", ColumnTypes.Int);
            _columnFails.Add("recoverymp", "mprecovery");
            _acceptedColumns.Add("mprecovery", ColumnTypes.Int);

            _acceptedColumns.Add("fixedpotential", ColumnTypes.Short);
            _acceptedColumns.Add("fixedgrade", ColumnTypes.Short);
            _acceptedColumns.Add("specialgrade", ColumnTypes.Short);
            _acceptedColumns.Add("grade", ColumnTypes.Short);


            _columnFails.Add("tradblock", "tradeblock");
            _acceptedColumns.Add("tradeblock", ColumnTypes.Byte);
            _acceptedColumns.Add("tradeavailable", ColumnTypes.Byte);
            _acceptedColumns.Add("equiptradeblock", ColumnTypes.Byte);
            _acceptedColumns.Add("accountsharetag", ColumnTypes.Byte);
            _columnFails.Add("accountshareable", "accountsharable");
            _acceptedColumns.Add("accountsharable", ColumnTypes.Byte);
            _acceptedColumns.Add("cashtradeblock", ColumnTypes.Byte);
            _acceptedColumns.Add("usetradeblock", ColumnTypes.Byte);
            _acceptedColumns.Add("sharableonce", ColumnTypes.Byte);
            _acceptedColumns.Add("notsale", ColumnTypes.Byte);
            _acceptedColumns.Add("nomovetolocker", ColumnTypes.Byte);

            _columnFails.Add("dropblock", "nodrop");
            _acceptedColumns.Add("nodrop", ColumnTypes.Byte);

            _columnFails.Add("noexpend", "notextend");
            _columnFails.Add("noextend", "notextend");
            _acceptedColumns.Add("notextend", ColumnTypes.Byte);
            _acceptedColumns.Add("epicitem", ColumnTypes.Byte);
            _acceptedColumns.Add("expireonlogout", ColumnTypes.Byte);
            _acceptedColumns.Add("superioreqp", ColumnTypes.Short);

            _acceptedColumns.Add("price", ColumnTypes.Int);
            _acceptedColumns.Add("cash", ColumnTypes.Byte);
            _acceptedColumns.Add("quest", ColumnTypes.Byte);
            _acceptedColumns.Add("only", ColumnTypes.Short);
            _acceptedColumns.Add("recovery", ColumnTypes.Short);
            _acceptedColumns.Add("enchantcategory", ColumnTypes.Short);
            _acceptedColumns.Add("timelimited", ColumnTypes.Short);


            _acceptedColumns.Add("charmexp", ColumnTypes.Short);
            _acceptedColumns.Add("willexp", ColumnTypes.Short);
            _acceptedColumns.Add("charismaexp", ColumnTypes.Short);
            _acceptedColumns.Add("senseexp", ColumnTypes.Short);
            _acceptedColumns.Add("craftexp", ColumnTypes.Short);
            _acceptedColumns.Add("insightexp", ColumnTypes.Short);


            _acceptedColumns.Add("durability", ColumnTypes.Short);
        }

        Dictionary<string, ColumnTypes> _acceptedColumns = new Dictionary<string, ColumnTypes>();
        Dictionary<string, string> _columnFails = new Dictionary<string, string>();

        List<Dictionary<string, object>> _items = new List<Dictionary<string, object>>();
        List<int> idlist = new List<int>();

        List<string> _failedColumns = new List<string>();

        public void HandleInfo(int currentID, WzSubProperty pProp)
        {
            Dictionary<string, object> columns = new Dictionary<string, object>();
            columns.Add("itemid", currentID);
            if (idlist.Contains(currentID))
            {
                Console.WriteLine(pProp.AbsolutePath);
                // Already done...
                return;
            }
            idlist.Add(currentID);

            foreach (IWzImageProperty prop2 in pProp.WzProperties)
            {
                var prop = prop2;
                if (prop is WzUOLProperty)
                    prop = prop.ToUOLLink();
                if (prop == null)
                    continue;
                var name = prop.Name.ToLower();

                if (_columnFails.ContainsKey(name)) name = _columnFails[name];

                if (!_acceptedColumns.ContainsKey(name))
                {
                    if (!_failedColumns.Contains(name))
                    {
                        _failedColumns.Add(name);
                        Console.WriteLine("Failed column: {0} | {1}", name, prop.AbsolutePath);
                    }
                    continue;
                }

                if (columns.ContainsKey(name) || name == "islot" || name == "afterimage" || name == "vslot") continue;

                if (prop is WzCompressedIntProperty || prop is WzStringProperty ||
                    prop is WzByteFloatProperty || prop is WzDoubleProperty ||
                    prop is WzUnsignedShortProperty)
                {
                    object value = null;
                    if (prop is WzCompressedIntProperty) value = prop.ToInt();
                    else if (prop is WzStringProperty) value = prop.ToStringValue();
                    else if (prop is WzByteFloatProperty) value = prop.ToFloat();
                    else if (prop is WzDoubleProperty) value = prop.ToDouble();
                    else if (prop is WzUnsignedShortProperty) value = prop.ToUnsignedShort();
                    else Console.WriteLine("wut");

                    columns.Add(name, value);
                }
            }
            if (columns.Count > 1)
            {
                _items.Add(columns);
            }
        }

        public void Finish(string pFilename)
        {
            StringBuilder sb = new StringBuilder();
            sb.AppendLine("DROP TABLE IF EXISTS phpVana_iteminfo;");
            sb.AppendLine();
            sb.AppendLine("CREATE TABLE phpVana_iteminfo (");
            bool first = true;
            foreach (var column in _acceptedColumns)
            {
                if (_columnFails.ContainsKey(column.Key)) continue;

                string coltype = "";
                if (column.Value == ColumnTypes.String) coltype = "VARCHAR(40)";
                else if (column.Value == ColumnTypes.Int) coltype = "INT";
                //else if (column.Value is double) coltype = "DOUBLE";
               // else if (column.Value is float) coltype = "FLOAT";
                else if (column.Value == ColumnTypes.Short) coltype = "SMALLINT";
                else if (column.Value == ColumnTypes.Byte) coltype = "TINYINT";
                else Console.WriteLine("wut");

                sb.AppendFormat("\t{2}`{0}` {1} NULL\r\n", column.Key, coltype, !first ? "," : "");
                first = false;
            }
            sb.AppendLine("\t,PRIMARY KEY (`itemid`)");
            sb.AppendLine(");");
            sb.AppendLine();
            sb.AppendLine();
            sb.AppendLine("INSERT INTO phpVana_iteminfo VALUES");

            bool first2 = true;
            foreach (var row in _items)
            {
                if (!first2)
                    sb.Append(',');
                first2 = false;
                sb.Append("(");

                first = true;
                foreach (var column in _acceptedColumns)
                {
                    if (_columnFails.ContainsKey(column.Key)) continue;

                    if (!first)
                        sb.Append(',');
                    first = false;

                    if (row.ContainsKey(column.Key))
                    {
                        string colvalue = "NULL";
                        if (column.Value == ColumnTypes.String) colvalue = MySql.Data.MySqlClient.MySqlHelper.EscapeString(row[column.Key].ToString());
                        else if (column.Value == ColumnTypes.Int) colvalue = row[column.Key].ToString();
                       // else if (column.Value is double) colvalue = row[column.Key].ToString();
                       // else if (column.Value is float) colvalue = row[column.Key].ToString();
                        else if (column.Value == ColumnTypes.Short) colvalue = row[column.Key].ToString();
                        else if (column.Value == ColumnTypes.Byte) colvalue = row[column.Key].ToString();


                        if (colvalue == "NULL")
                            sb.AppendFormat("NULL");
                        else
                            sb.AppendFormat("'{0}'", colvalue);
                    }
                    else
                    {
                        sb.Append("NULL");
                    }
                }
                sb.Append(")\r\n");
            }

            System.IO.File.WriteAllText(pFilename, sb.ToString());
        }
    }
}
