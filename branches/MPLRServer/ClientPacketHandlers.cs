using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class ClientPacketHandlers
    {
        public static void HandleVersion(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte locale = pPacket.ReadByte();
            ushort version = pPacket.ReadUShort();
            ushort subversion = pPacket.ReadUShort();

            Logger.WriteLine("Client data: {0} {1}.{2}", locale, version, subversion);
            pConnection.MapleVersion = version;
            if (pConnection.CharData != null)
            {
                pConnection.Save();
            }
            pConnection.CharData = null; // Back to the LoginServer!!!
        }
    }
}
