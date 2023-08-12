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
bot.remove_command('help')

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
    response = requests.get(url+"/api/admin/statistics?api_key="+api_key)
    if response.status_code == 200:
        data = response.json()
        client_users = data['statistics']['users']
        client_tickets = data['statistics']['tickets']
        embed = discord.Embed(title="Status about the host", color=discord.Color.green())
        embed.set_thumbnail(url=logo)
        embed.add_field(name="Total users", value=client_users, inline=False)
        embed.add_field(name="Total tickets", value=client_tickets, inline=False)
    elif response.status_code == 403:
        embed.add_field(name="Error", value="We can't find this user in the database", inline=False)
    elif response.status_code == 500:
        embed.add_field(name="Error", value="Failed to get connection info from API.", inline=False)
    else:
        embed = discord.Embed(title="Connection Info", color=discord.Color.red())
        embed.set_thumbnail(url=logo)
        embed.add_field(name="Error", value="Failed to get connection info from API.", inline=False)
    embed.set_footer(text=f"© Copyright 2021-2023 MythicalSystems")
    await ctx.respond(embed=embed)

@bot.slash_command(description="Get information about a user", guild_ids=[dscserver])
async def userinfo(ctx, email_address: str):
    #if str(ctx.author.id) not in allowed_users:
    #    await ctx.respond("Sorry, you are not allowed to use this command.")
    #    return

    response = requests.get(url + "/api/admin/user/info?api_key=" + api_key + "&email=" + email_address)
    embed = discord.Embed(title="User Info", color=discord.Color.red())

    if response.status_code == 200:
        data = response.json()
        client_username = data['info']['username']
        client_avatar = data['info']['avatar']
        client_email = data['info']['email']
        client_role = data['info']['role']
        client_registred = data['info']['registred_at']
        client_banned = data['info']['banned']

        embed = discord.Embed(
            title=client_username + " | User Info",
            description=f"Here is everything we know about {client_username}",
            color=discord.Color.green())
        embed.set_author(name=client_username, icon_url=client_avatar)
        embed.set_thumbnail(url=client_avatar)
        embed.add_field(name="Username", value=f"```{client_username}```", inline=False)
        embed.add_field(name="Email", value=f"```{client_email}```", inline=False)
        embed.add_field(name="Role", value=f"```{client_role}```", inline=False)
        embed.add_field(name="Registred", value=f"```{client_registred}```", inline=False)
        
        if client_banned:
            embed.add_field(name="This user is banned for:", value=f"```{client_banned}```", inline=False)
        
    elif response.status_code == 403:
        embed.add_field(name="Error", value="We can't find this user in the database", inline=False)
    elif response.status_code == 500:
        embed.add_field(name="Error", value="Failed to get connection info from API.", inline=False)
    else:
        embed.add_field(name="Error", value=f"An error occurred. Status Code: {response.status_code}", inline=False)
    
    embed.set_thumbnail(url=logo)
    embed.set_footer(text="© Copyright 2021-2023 MythicalSystems")
    await ctx.respond(embed=embed)

@bot.slash_command(description="Get information about a user", guild_ids=[dscserver])
async def resources(ctx, email_address: str):
    #if str(ctx.author.id) not in allowed_users:
    #    await ctx.respond("Sorry, you are not allowed to use this command.")
    #    return

    response = requests.get(url + "/api/admin/user/info?api_key=" + api_key + "&email=" + email_address)
    embed = discord.Embed(title="User Info", color=discord.Color.red())

    if response.status_code == 200:
        data = response.json()
        client_username = data['info']['username']
        client_avatar = data['info']['avatar']
        client_banned = data['info']['banned']
        client_coins = data['resources']['coins']
        client_ram = data['resources']['ram']
        client_disk = data['resources']['disk']
        client_cpu = data['resources']['cpu']
        client_server_limit = data['resources']['server_limit']
        client_ports = data['resources']['ports']
        client_databases = data['resources']['databases']
        client_backups = data['resources']['backups']
        
        embed = discord.Embed(
            title=client_username + " | User Info",
            description=f"Here is everything we know about {client_username}",
            color=discord.Color.green())
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
        embed.add_field(name="Error", value="We can't find this user in the database", inline=False)
    elif response.status_code == 500:
        embed.add_field(name="Error", value="Failed to get connection info from API.", inline=False)
    else:
        embed.add_field(name="Error", value=f"An error occurred. Status Code: {response.status_code}", inline=False)
    
    embed.set_thumbnail(url=logo)
    embed.set_footer(text="© Copyright 2021-2023 MythicalSystems")
    await ctx.respond(embed=embed)

bot.add_application_command(admin)
bot.run(token)
