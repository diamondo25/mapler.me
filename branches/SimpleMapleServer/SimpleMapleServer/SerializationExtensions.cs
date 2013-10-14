using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Runtime.Serialization;
using System.IO;
using System.Xml;

public static class SerializationExtensions
{
    public static string Serialize<T>(this T obj)
    {
        var serializer = new DataContractSerializer(obj.GetType());
        using (var writer = new StringWriter())
        using (var stm = new XmlTextWriter(writer))
        {
            stm.Indentation = 1;
            stm.IndentChar = '\t';
            stm.Formatting = Formatting.Indented;
            serializer.WriteObject(stm, obj);
            return writer.ToString();
        }
    }
    public static T Deserialize<T>(this string serialized)
    {
        var serializer = new DataContractSerializer(typeof(T));
        using (var reader = new StringReader(serialized))
        using (var stm = new XmlTextReader(reader))
        {
            return (T)serializer.ReadObject(stm);
        }
    }
}