using System;

namespace MythicalDash
{
    public class Program
    {
        public static string ascii = @" 
  __  __       _   _     _           _ _____            _     
 |  \/  |     | | | |   (_)         | |  __ \          | |    
 | \  / |_   _| |_| |__  _  ___ __ _| | |  | | __ _ ___| |__  
 | |\/| | | | | __| '_ \| |/ __/ _` | | |  | |/ _` / __| '_ \ 
 | |  | | |_| | |_| | | | | (_| (_| | | |__| | (_| \__ \ | | |
 |_|  |_|\__, |\__|_| |_|_|\___\__,_|_|_____/ \__,_|___/_| |_|
          __/ |                                               
         |___/                                                                                                                 
";
        public static string version = "1.0.0";
        public static bool skiposcheck = true;
        public static Logger logger = new Logger();
        public static void Main(string[] args)
        {
            Console.Clear();
            Console.WriteLine(ascii);
            if (skiposcheck == false) {
                if (!System.OperatingSystem.IsLinux())
                {
                    logger.Log(LogType.Error, "Sorry but this app runs on linux!");
                    Environment.Exit(0x0);
                }
            }
            if (args.Contains("-version"))
            {
                logger.Log(LogType.Info, "You are running version: " + version);
                Environment.Exit(0x0);
            }
            else if (args.Length > 0)
            {
                logger.Log(LogType.Error, "This is an invalid startup argument. Please use '-help' to get more information");
                Environment.Exit(0x0);
            } else
            {
                logger.Log(LogType.Error, "This is an invalid startup argument. Please use '-help' to get more information");
                Environment.Exit(0x0);
            }
        }
    }

}