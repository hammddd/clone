

 
 
  
  async function getBlogPosts(pageNumber) {
      try {
          const response = await fetch(`http://localhost/clone/backend/index.php/blog?page=${pageNumber}`);
          
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
  
          const blogPosts = await response.json();
  
          const blogPostsContainer = document.getElementById('blog-posts-container');
          blogPostsContainer.innerHTML = ''; // Clear any existing content
  
          if (blogPosts.length === 0) {
              blogPostsContainer.innerHTML = '<p>No blog posts available.</p>';
              return;
          }
  
          blogPosts.forEach(post => {
              const postElement = document.createElement('div');
              postElement.className = 'blog-post clearfix';
              postElement.innerHTML = `
                  <div class='blog-body'>
                      <h3 class='MediumHeader'>
                          <a href="#" onclick="fetchAndDisplayBlogPostsByIds(['${post.id}']); return false;">${post.title}</a>
                      </h3>
                      <div class='blog-post-date'>${new Date(post.post_date).toLocaleDateString()}</div>
                      <div class='blog-post-tags'></div>
                      <div class='post-body' data-sbx-post-href='${post.url}'>
                          <div class='image-block-align-center image-add-border large'>
                              ${post.image_url ? `<img src="${post.image_url}" alt="${post.caption || 'Blog Image'}">` : ''}
                              ${post.caption ? `<div class='caption'>${post.caption}</div>` : ''}
                          </div>
                          <div class='post-text'>
                              <p class="from_wysiwyg">${post.content}</p>
                          </div>
                          
                      </div>
                  </div>
              `;
              blogPostsContainer.appendChild(postElement);
          });
  
          // Update pagination with the correct total pages
          updatePagination(pageNumber, 100); // Assuming there are 100 pages
  
          // Update the browser's address bar without reloading the page
          history.pushState(null, '', `blog.html?page=${pageNumber}`);
  
      } catch (error) {
          console.error('Error fetching blog posts:', error);
          const blogPostsContainer = document.getElementById('blog-posts-container');
          blogPostsContainer.innerHTML = '<p class="error-message">Failed to load blog posts. Please try again later.</p>';
      }
  }

  
  // Function to fetch and render tags
function fetchAndRenderTags() {
  fetch('http://localhost/clone/backend/index.php/tags')
      .then(response => {
          if (!response.ok) {
              throw new Error('Network response was not ok');
          }
          return response.json();
      })
      .then(data => {
          const tagList = document.getElementById('tagList');
          tagList.innerHTML = ''; // Clear any existing content

          if (data.length === 0) {
              tagList.innerHTML = '<li>No tags found.</li>';
              return;
          }

          data.forEach(tag => {
              const listItem = document.createElement('li');
              const anchor = document.createElement('a');

              // URL encoding the tag name for the link
              const encodedTagName = encodeURIComponent(tag.tag_name);
              anchor.href = `#`; // Prevent default link behavior
              anchor.textContent = `${tag.tag_name} (${tag.count})`;

              // Add click event listener to the anchor tag
              anchor.addEventListener('click', () => {
                  const blogPostIds = tag.blog_posts.split(',').map(id => id.trim());
                  fetchAndDisplayBlogPostsByIds(blogPostIds);
              });

              // Append anchor to the list item and list item to the tag list
              listItem.appendChild(anchor);
              tagList.appendChild(listItem);
          });
      })
      .catch(error => {
          console.error('Error fetching tags:', error);
          const tagList = document.getElementById('tagList');
          tagList.innerHTML = '<li>Error loading tags. Please try again later.</li>';
      });
}









// Function to fetch and display blog posts by multiple IDs
function fetchAndDisplayBlogPostsByIds(ids) {
  const blogPostsContainer = document.getElementById('blog-posts-container');
  blogPostsContainer.innerHTML = ''; // Clear any existing content

  const fetchPromises = ids.map(id => 
      fetch(`http://localhost/clone/backend/index.php/blog?id=${id}`)
          .then(response => {
              if (!response.ok) {
                  throw new Error(`Failed to fetch blog post with ID ${id}`);
              }
              return response.json();
          })
  );

  Promise.all(fetchPromises)
      .then(posts => {
          posts.forEach(post => {
              if (!post) {
                  return;
              }

              const postElement = document.createElement('div');
              postElement.className = 'blog-post clearfix';
              postElement.innerHTML = `
                  <div class='blog-body'>
                     <h3 class='MediumHeader'>
                          <a href="#" onclick="fetchAndDisplayBlogPostsByIds(['${post.id}']); return false;">${post.title}</a>
                      </h3>
                      <div class='blog-post-date'>${new Date(post.post_date).toLocaleDateString()}</div>
                      <div class='blog-post-tags'></div>
                      <div class='post-body' data-sbx-post-href='${post.url}'>
                          <div class='image-block-align-center image-add-border large'>
                              ${post.image_url ? `<img src="${post.image_url}" alt="${post.caption || 'Blog Image'}">` : ''}
                              ${post.caption ? `<div class='caption'>${post.caption}</div>` : ''}
                          </div>
                          <div class='post-text'>
                              <p class="from_wysiwyg">${post.content}</p>
                          </div>
                        
                      </div>
                  </div>
              `;

              // Add an event listener for the title click
              postElement.querySelector('a').addEventListener('click', (e) => {
                  e.preventDefault();
                  getSingleBlogPost(post.url);
              });
              blogPostsContainer.appendChild(postElement);
          });
      })
      .catch(error => {
          console.error('Error fetching blog posts:', error);
          blogPostsContainer.innerHTML = '<p class="error-message">Failed to load blog posts. Please try again later.</p>';
      });
}









// Function to fetch and render archives
function fetchAndRenderArchives() {
  fetch('http://localhost/clone/backend/index.php/archives')
      .then(response => {
          if (!response.ok) {
              throw new Error('Network response was not ok');
          }
          return response.json();
      })
      .then(data => {
          console.log('Archives data:', data); // Debugging log to check the received data
          const archiveList = document.getElementById('archiveList');
          archiveList.innerHTML = ''; // Clear any existing content

          if (data.length === 0) {
              archiveList.innerHTML = '<li>No archives found.</li>';
              return;
          }

          data.forEach(archive => {
              const listItem = document.createElement('li');
              const anchor = document.createElement('a');

              // Format the archive month and post count
              anchor.textContent = `${formatMonth(archive.month)} (${archive.post_count})`;
              anchor.href = '#'; // Prevent the default behavior
              anchor.addEventListener('click', (e) => {
                  e.preventDefault();
                  // Fetch and display blog posts by IDs when an archive is clicked
                  fetchAndDisplayBlogPostsByIds(archive.blog_posts.split(','));
              });

              // Append anchor to the list item and list item to the archive list
              listItem.appendChild(anchor);
              archiveList.appendChild(listItem);
          });
      })
      .catch(error => {
          console.error('Error fetching archives:', error); // Debugging log for errors
          const archiveList = document.getElementById('archiveList');
          archiveList.innerHTML = '<li>Error loading archives. Please try again later.</li>';
      });
}

// Helper function to format the month from "YYYY-MM" to "Month YYYY"
function formatMonth(month) {
  const date = new Date(month + '-01'); // Add a day to make it a valid date
  const options = { year: 'numeric', month: 'long' };
  return date.toLocaleDateString(undefined, options);
}






















window.onload = () => {
fetchAndRenderArchives();
  fetchAndRenderTags();
   
  const urlParams = new URLSearchParams(window.location.search);
  const page = parseInt(urlParams.get('page')) || 1; // Get page number from URL or default to 1
  getBlogPosts(page);
};









  
  // Function to handle editing a blog post
  function editPost(postId) {
      window.location.href = `edit.html?id=${postId}`;
  }












  
  // Function to handle deleting a blog post
  async function deletePost(postId) {
      const confirmDelete = confirm("Are you sure you want to delete this post?");
      if (!confirmDelete) {
          return;
      }
  
      try {
          const response = await fetch(`http://localhost/clone/backend/index.php/blog?id=${postId}`, {
              method: 'DELETE',
          });
  
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
  
          const result = await response.json();
  
          if (result.message === "Blog post deleted successfully!") {
              alert("Blog post deleted successfully!");
              getBlogPosts(1); // Refresh the blog post list
          } else {
              alert("Failed to delete blog post. Please try again.");
          }
      } catch (error) {
          console.error('Error deleting the blog post:', error);
          alert("An error occurred while deleting the blog post. Please try again later.");
      }
  }
  



  // Function to handle updating pagination links
  function updatePagination(currentPage, totalPages) {
      const paginationContainer = document.querySelector('.pagination ul');
      paginationContainer.innerHTML = ''; // Clear existing pagination
  
      const maxVisiblePages = 5;
      const startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
      const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
  
      if (currentPage > 1) {
          paginationContainer.innerHTML += `
              <li class='page previous'>
                  <a href="blog.html?page=${currentPage - 1}" onclick="getBlogPosts(${currentPage - 1}); return false;">Previous</a>
              </li>
          `;
      }
  
      for (let i = startPage; i <= endPage; i++) {
          paginationContainer.innerHTML += `
              <li class='page ${i === currentPage ? 'current' : ''}'>
                  <a href="blog.html?page=${i}" onclick="getBlogPosts(${i}); return false;">${i}</a>
              </li>
          `;
      }
  
      if (currentPage < totalPages) {
          paginationContainer.innerHTML += `
              <li class='page next'>
                  <a href="blog.html?page=${currentPage + 1}" onclick="getBlogPosts(${currentPage + 1}); return false;">Next</a>
              </li>
          `;
      }
  }




  
  
  