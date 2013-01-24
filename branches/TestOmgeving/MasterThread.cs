using System;
using System.Collections.Generic;
using System.Collections.Concurrent;
using System.Linq;
using System.Text;

using System.Threading;

namespace Mapler_Client
{
    public class MasterThread
    {
        public static MasterThread Instance { get; private set; }

        public static DateTime CurrentDate { get; private set; }

        public ulong TicksPerSecond { get; private set; }
        public ulong TicksBeforeSleep { get; set; }
        public ulong CurrentTickCount { get; private set; }
        public bool Stop { get; set; }
        public string ServerName { get; private set; }

        private ConcurrentQueue<Action<DateTime>> _callbacks = new ConcurrentQueue<Action<DateTime>>();

        private Thread _mainThread;

        List<ulong> _performanceCounter = new List<ulong>();

        public MasterThread(string pServerName, ulong pTicksBeforeSleep)
        {
            ServerName = pServerName;
            TicksBeforeSleep = pTicksBeforeSleep;
            Stop = false;

        }

        public static void Load(string pServerName, ulong pTicksBeforeSleep = 150)
        {
            Instance = new MasterThread(pServerName, pTicksBeforeSleep);
            Instance.Init();
        }

        public void Init()
        {
            _mainThread = new Thread(Run);
            _mainThread.IsBackground = true;
            _mainThread.Name = "MasterThread - thread";
            _mainThread.Priority = ThreadPriority.AboveNormal;
            _mainThread.Start();
        }

        public void AddCallback(Action<DateTime> pAction)
        {
            _callbacks.Enqueue(pAction);
        }

        private void Run()
        {
            Action<DateTime> action;
            CurrentDate = DateTime.Now;
            try
            {
                for (CurrentTickCount = 0; !Stop; CurrentTickCount++)
                {
                    while (_callbacks.TryDequeue(out action))
                    {
                        try
                        {
                            action(CurrentDate);
                        }
                        catch (Exception ex)
                        {
                            Logger.WriteLine("Caught an exception inside the MainThread thread while running an action. Please, handle the exceptions yourself!\r\n{0}", ex.ToString());
                        }
                    }

                    if (CurrentTickCount % TicksBeforeSleep == 0)
                    {
                        CurrentDate = DateTime.Now;
                        Thread.Sleep(1);
                    }
                }

            }
            catch { }
            _mainThread = null;
        }
    }
}