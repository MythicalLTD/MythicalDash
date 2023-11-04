import discord
from discord.ext import commands
from colorama import Fore, Style, init
import sys
from config import token, prefix,activityname,dscserver,name,logo,api_key,url,allowed_users
import requests


init(autoreset=True)

ascii_art = r"""
  __  __       _   _     _           _ _____            _     
 |  \/  |     | | | |   (_)         | |  __ \          | |    
 | \  / |_   _| |_| |__  _  ___ __ _| | |  | | __ _ ___| |__  
 | |\/| | | | | __| '_ \| |/ __/ _` | | |  | |/ _` / __| '_ \ 
 | |  | | |_| | |_| | | | | (_| (_| | | |__| | (_| \__ \ | | |
 |_|  |_|\__, |\__|_| |_|_|\___\__,_|_|_____/ \__,_|___/_| |_|
          __/ |                                               
         |___/                                                
"""

rainbow_colors = [Fore.RED, Fore.YELLOW, Fore.GREEN, Fore.CYAN, Fore.BLUE, Fore.MAGENTA]

if sys.platform != "linux":
    print("This bot only runs on Linux.")
    sys.exit(1) 

bot = commands.Bot(command_prefix=commands.when_mentioned_or(prefix), activity=discord.Game(name=activityname), intents=discord.Intents.all())
admin = discord.SlashCommandGroup("admin", "Admin commands")
redeem = discord.SlashCommandGroup("redeem", "Redeem commands")

#bot.remove_command('help')

@bot.event
async def on_ready():
    art_lines = ascii_art.splitlines()
    for i, line in enumerate(art_lines):
        color = rainbow_colors[i % len(rainbow_colors)]
        bold_line = Style.BRIGHT + line 
        print(color + bold_line)

    print(Style.RESET_ALL)  
    print('Logged in as: ' + str(bot.user))
    print('Discord ID: ' + str(bot.user.id))

@bot.slash_command(description="Show the ping of the bot", guild_ids=[dscserver])
async def ping(ctx):
    latency = ctx.bot.latency
    latency = latency * 1000
    latency = round(latency)
    await ctx.respond(":ping_pong: Pong! My ping is: " + "**{}ms**!".format(latency))


@bot.slash_command(description="Get status from client", guild_ids=[dscserver])
async def status(ctx):
    headers = {
        "Authorization": f"{api_key}",
    }
    
    response = requests.get(f"{url}/api/admin/statistics", headers=headers)
    
    embed = discord.Embed(title="Status about the host", color=discord.Color.green())
    embed.set_thumbnail(url=logo)

    if response.status_code == 200:
        data = response.json()
        client_users = data.get("data", {}).get("users", "Unknown")
        client_tickets = data.get("data", {}).get("tickets", "Unknown")
        client_svs = data.get("data", {}).get("servers", "Unknown")
        client_locs = data.get("data", {}).get("locations", "Unknown")
        client_eggs = data.get("data", {}).get("eggs", "Unknown")
        
        embed.add_field(name="Total users", value=client_users, inline=False)
        embed.add_field(name="Total tickets", value=client_tickets, inline=False)
        embed.add_field(name="Total servers", value=client_svs, inline=False)
        embed.add_field(name="Total locations", value=client_locs, inline=False)
        embed.add_field(name="Total eggs", value=client_eggs, inline=False)
        
        
    elif response.status_code == 403:
        embed.add_field(name="Error", value="Access Denied. Check your permissions.", inline=False)
    elif response.status_code == 500:
        embed.add_field(name="Error", value="Failed to get connection info from API.", inline=False)
    else:
        embed = discord.Embed(title="Connection Info", color=discord.Color.red())
        embed.set_thumbnail(url=logo)
        embed.add_field(name="Error", value=f"An error occurred. Status Code: {response.status_code}", inline=False)

    embed.set_footer(text=f"© Copyright 2021-2023 MythicalSystems")
    await ctx.respond(embed=embed)

@bot.slash_command(description="Get information about a user", guild_ids=[dscserver])
async def userinfo(ctx, email_address: str):
    headers = {
        "Authorization": f"{api_key}",  
    }
    response = requests.get(f"{url}/api/admin/user/info?email={email_address}", headers=headers)
    
    embed = discord.Embed(title="User Info", color=discord.Color.red())

    if response.status_code == 200:
        data = response.json()
        user_info = data.get("data", {}).get("info", {})

        username = user_info.get("username", "Unknown")
        email = user_info.get("email", "Unknown")
        role = user_info.get("role", "Unknown")
        registred_at = user_info.get("registred_at", "Unknown")
        banned = user_info.get("banned", "")

        embed = discord.Embed(
            title=f"{username} | User Info",
            description=f"Here is everything we know about {username}",
            color=discord.Color.green()
        )
        embed.set_thumbnail(url=user_info.get("avatar", logo))
        embed.add_field(name="Username", value=f"```{username}```", inline=False)
        embed.add_field(name="Email", value=f"```{email}```", inline=False)
        embed.add_field(name="Role", value=f"```{role}```", inline=False)
        embed.add_field(name="Registered", value=f"```{registred_at}```", inline=False)
        
        if banned:
            embed.add_field(name="Banned for", value=f"```{banned}```", inline=False)
    else:
        embed.add_field(name="Error", value=f"An error occurred. Status Code: {response.status_code}", inline=False)

    embed.set_thumbnail(url=logo)
    embed.set_footer(text="© Copyright 2021-2023 MythicalSystems")
    await ctx.respond(embed=embed)

@bot.slash_command(description="Get information about a user resources", guild_ids=[dscserver])
async def resources(ctx, email_address: str):
    headers = {
        "Authorization": f"{api_key}",  # Replace with your actual authorization token
    }

    response = requests.get(f"{url}/api/admin/user/info?email={email_address}", headers=headers)
    embed = discord.Embed(title="User Info", color=discord.Color.red())

    if response.status_code == 200:
        data = response.json()
        user_info = data.get("data", {}).get("info", {})
        resources = data.get("data", {}).get("resources", {})
        
        client_username = user_info.get("username", "Unknown")
        client_avatar = user_info.get("avatar", logo)
        client_banned = user_info.get("banned", "")
        client_coins = resources.get("coins", "Unknown")
        client_ram = resources.get("ram", "Unknown")
        client_disk = resources.get("disk", "Unknown")
        client_cpu = resources.get("cpu", "Unknown")
        client_server_limit = resources.get("server_limit", "Unknown")
        client_ports = resources.get("ports", "Unknown")
        client_databases = resources.get("databases", "Unknown")
        client_backups = resources.get("backups", "Unknown")

        embed = discord.Embed(
            title=f"{client_username} | User Info",
            description=f"Here is everything we know about {client_username}",
            color=discord.Color.green()
        )
        embed.set_author(name=client_username, icon_url=client_avatar)
        embed.set_thumbnail(url=client_avatar)
        embed.add_field(name="Username", value=f"```{client_username}```", inline=False)
        embed.add_field(name="Coins", value=f"```{client_coins}```", inline=True)
        embed.add_field(name="Ram", value=f"```{client_ram}```", inline=True)
        embed.add_field(name="Disk", value=f"```{client_disk}```", inline=True)
        embed.add_field(name="Cpu", value=f"```{client_cpu}```", inline=True)
        embed.add_field(name="Server Limit", value=f"```{client_server_limit}```", inline=True)
        embed.add_field(name="Allocations", value=f"```{client_ports}```", inline=True)
        embed.add_field(name="Databases", value=f"```{client_databases}```", inline=True)
        embed.add_field(name="Backups", value=f"```{client_backups}```", inline=True)
        
        if client_banned:
            embed.add_field(name="This user is banned for:", value=f"```{client_banned}```", inline=False)
        
    elif response.status_code == 403:
        embed.add_field(name="Error", value="Access Denied. Check your permissions.", inline=False)
    elif response.status_code == 500:
        embed.add_field(name="Error", value="Failed to get connection info from API.", inline=False)
    else:
        embed.add_field(name="Error", value=f"An error occurred. Status Code: {response.status_code}", inline=False)
    
    embed.set_thumbnail(url=logo)
    embed.set_footer(text=f"© Copyright 2021-2023 MythicalSystems")
    await ctx.respond(embed=embed)

bot.add_application_command(admin)
bot.add_application_command(redeem)

bot.run(token)
