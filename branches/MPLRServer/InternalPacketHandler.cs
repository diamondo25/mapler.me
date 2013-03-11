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
                pConnection.Logger_WriteLine("- Client got connection with MapleStory server @ {0}:{1}", ip, port);
                if (port == 8484)
                {
                    pConnection.SendInfoText("MapleStory connected to the login server");
                }
                else
                {
                    pConnection.SendInfoText("MapleStory connected to the Game or CashShop server");
                }
            }
            else
            {
                pConnection.Logger_WriteLine("- Client lost connection with MapleStory server");
                pConnection.SendInfoText("MapleStory lost connection with the server.");
            }
        }
    }
}
