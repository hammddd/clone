import requests
from bs4 import BeautifulSoup
import mysql.connector
from datetime import datetime
import time

# Database connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="1234",
    database="blog_db"
)
cursor = db.cursor()

# Function to update the caption for a blog post
def update_blog_post_caption(post_id, caption):
    cursor.execute(
        "UPDATE blog_posts SET caption = %s WHERE id = %s",
        (caption, post_id)
    )
    db.commit()

# Function to scrape and update captions for blog posts with images
def scrape_captions():
    # Get all blog posts with an image URL but without a caption and with post_id > 52
    cursor.execute("""
        SELECT id, url, image_url 
        FROM blog_posts 
        WHERE image_url IS NOT NULL 
        AND (caption IS NULL OR caption = '') 
        AND id > 52
    """)
    blog_posts = cursor.fetchall()

    for post_id, post_url, image_url in blog_posts:
        full_url = f"https://www.robinhobb.com{post_url}"
        print(f"Fetching {full_url}...")

        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36"
        }
        response = requests.get(full_url, headers=headers)

        if response.status_code == 200:
            soup = BeautifulSoup(response.text, 'html.parser')

            # Find the image element with the same URL
            image_element = soup.find('img', src=image_url)
            if image_element:
                # Extract the caption if it exists
                caption_element = image_element.find_next_sibling('div', class_='caption')
                caption = caption_element.get_text(strip=True) if caption_element else None

                if caption:
                    # Update the caption in the database
                    print(f"Updating caption for post {post_id}: {caption}")
                    update_blog_post_caption(post_id, caption)
                else:
                    print(f"No caption found for post {post_id}")
            else:
                print(f"Image not found in HTML for post {post_id}")

        else:
            print(f"Failed to fetch {full_url} with status code: {response.status_code}")
        time.sleep(2)  # Add a delay between requests to avoid getting blocked

# Start the caption scraping process
scrape_captions()

# Close the database connection
cursor.close()
db.close()
