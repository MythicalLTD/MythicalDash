using System.Security.Cryptography;
using System.Text;

namespace MythicalDash
{
    public class KeyChecker {
        public static bool isStrongKey(string password, int minimumLength = 8)
        {
            bool hasUppercase = false;
            bool hasLowercase = false;
            bool hasDigit = false;

            if (password.Length < minimumLength)
                return false;

            foreach (char c in password)
            {
                if (char.IsUpper(c))
                    hasUppercase = true;
                else if (char.IsLower(c))
                    hasLowercase = true;
                else if (char.IsDigit(c))
                    hasDigit = true;
            }

            return hasUppercase && hasLowercase && hasDigit;
        }
        public static string GenerateStrongKey(int length = 32)
        {
            const string uppercaseChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            const string lowercaseChars = "abcdefghijklmnopqrstuvwxyz";
            const string digitChars = "0123456789";
            string validChars = uppercaseChars + lowercaseChars + digitChars;
            #pragma warning disable
            using (RNGCryptoServiceProvider rng = new RNGCryptoServiceProvider())
            {
                byte[] randomBytes = new byte[length];
                rng.GetBytes(randomBytes);

                StringBuilder sb = new StringBuilder(length);
                foreach (byte b in randomBytes)
                {
                    sb.Append(validChars[b % validChars.Length]);
                }

                return sb.ToString();
            }
            #pragma warning restore
        }
    }
}