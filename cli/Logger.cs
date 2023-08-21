namespace MythicalDash
{
    public enum LogType
    {
        Info,
        Warning,
        Error
    }

    public class Logger
    {
        private string logFilePath;

        public Logger()
        {
            string logDirectory = "logs";
            string logFileName = "log.txt";
            string logDirectoryPath = Path.Combine(Directory.GetCurrentDirectory(), logDirectory);
            logFilePath = Path.Combine(logDirectoryPath, logFileName);
            Directory.CreateDirectory(logDirectoryPath);
            RenameLogFile();
        }

        public void Log(LogType type, string message)
        {
            string timestamp = DateTime.Now.ToString("HH:mm:ss");
            string logText = $"[{timestamp}] [{type.ToString()}] {message}";

            ConsoleColor color = ConsoleColor.White;
            switch (type)
            {
                case LogType.Info:
                    color = ConsoleColor.Green;
                    break;
                case LogType.Warning:
                    color = ConsoleColor.Yellow;
                    break;
                case LogType.Error:
                    color = ConsoleColor.Red;
                    break;
            }
            Console.ForegroundColor = color;
            Console.WriteLine(logText);
            Console.ResetColor();

            AppendToFile(logText);
        }

        private void AppendToFile(string logText)
        {
            try
            {
                using (StreamWriter writer = File.AppendText(logFilePath))
                {
                    writer.WriteLine(logText);
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error writing to log file: {ex.Message}");
            }
        }

        private void RenameLogFile()
        {
            if (File.Exists(logFilePath))
            {
                string logFileNameWithoutExtension = Path.GetFileNameWithoutExtension(logFilePath);
                string logFileExtension = Path.GetExtension(logFilePath);
#pragma warning disable
                string logDirectoryPath = Path.GetDirectoryName(logFilePath);
                string newLogFileName = GetUniqueLogFileName(logDirectoryPath, logFileNameWithoutExtension, logFileExtension);
#pragma warning restore
                string newLogFilePath = Path.Combine(logDirectoryPath, newLogFileName);
                File.Move(logFilePath, newLogFilePath);
            }
        }

        private string GetUniqueLogFileName(string directoryPath, string fileNameWithoutExtension, string fileExtension)
        {
            string uniqueFileName = $"{fileNameWithoutExtension}-{DateTime.Now:yyyy-MM-dd}{fileExtension}";
            string uniqueFilePath = Path.Combine(directoryPath, uniqueFileName);

            int counter = 1;
            while (File.Exists(uniqueFilePath))
            {
                uniqueFileName = $"{fileNameWithoutExtension}-{counter++}-{DateTime.Now:yyyy-MM-dd}{fileExtension}";
                uniqueFilePath = Path.Combine(directoryPath, uniqueFileName);
            }

            return uniqueFileName;
        }
    }
}