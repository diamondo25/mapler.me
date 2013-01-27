using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class InternalPacketHandler
    {

        public static void HandleServerConnectionStatus(ClientConnection pConnection, MaplePacket pPacket)
        {
            if (pPacket.ReadBool())
            {
                string ip = pPacket.ReadString();
                ushort port = pPacket.ReadUShort();
                Logger.WriteLine("- Client got connection with MapleStory server @ {0}:{1}", ip, port);
            }
            else
            {
                Logger.WriteLine("- Client lost connection with MapleStory server");
                pConnection.Save(true);
            }
        }
    }
}
