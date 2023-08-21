namespace MythicalDash
{
    public class FileManager
    {
        public bool Exist()
        {
            string filePath = "config.yml";

            if (File.Exists(filePath))
            {
                return true;
            } else {
                return false;
            }
        }
    }
}