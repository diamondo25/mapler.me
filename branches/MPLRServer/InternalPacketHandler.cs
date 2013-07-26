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

                pConnection.ConnectedToIP = ip;
                pConnection.ConnectedToPort = port;

                if (port == 8484)
                {
                    pConnection.SendInfoText("Mapler.me is awaiting account check! Happy mapling!");
                    pConnection.WorldID = 255;
                    pConnection.ChannelID = 255;
                }
                else
                {
                    pConnection.SendInfoText("You successfully connected, or are in the Cash Shop!");
                }

                pConnection.ConnectedTimeToServer = MasterThread.CurrentDate;
            }
            else
            {
                pConnection.Logger_WriteLine("- Client lost connection with MapleStory server");
                pConnection.SendInfoText("Maplestory is closed, or not connected properly.");

                pConnection.ConnectedToIP = "0.0.0.0";
                pConnection.ConnectedToPort = 0;

                if (pConnection.ConnectedTimeToServer != DateTime.MinValue)
                {
                    var timespan = MasterThread.CurrentDate - pConnection.ConnectedTimeToServer;
                    pConnection.Logger_WriteLine("Player was connected for {0}", timespan);

                    if (timespan.TotalSeconds < 5)
                    {
                        pConnection.Logger_WriteLine("CLIENT PROBABLY FAILED TO CONNECT!!!");
                    }

                    if (pConnection.CharData != null)
                    {
                        // Probably CC-ing or something. record

                        MySQL_Connection.Instance.RunQuery("INSERT INTO connection_log VALUES " + MySQL_Connection.BuildValuesRow(pConnection.AccountID, pConnection.CharacterInternalID, pConnection.ChannelID, pConnection.ConnectedTimeToServer, new MySQL_Connection.NowType()));

                    }
                }

                pConnection.CharData = null;
                pConnection.CharacterInternalID = -1;
                pConnection.CharacterID = -1;
                pConnection.ChannelID = 255;
            }
        }
    }
}
