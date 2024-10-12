# User Comment Statistics Block

## Description

This module provides a custom block to display user comment statistics in Drupal. It allows you to visualize relevant information about commenting activity on your site.

## Features

- Displays comment statistics per user.
- Configurable through Drupal's administration interface.
- Easy integration with other Drupal modules and themes.

## Installation

### Method 1: Composer (Recommended)

1. Add the repository to your `composer.json`:

    ```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/tantrumTP/user_comment_stats.git"
        }
    ]
    ```

2. Run the following command:

    ```bash
    composer require tantrum/user_comment_stats
    ```

3. Enable the module with Drush or from the Drupal module administration page (/admin/modules).

### Method 2: Manual

1. Download the module and place it in the `modules/custom` directory of your Drupal installation.
2. Enable the module with Drush or from the Drupal module administration page (/admin/modules).

## Configuration

1. Go to the block configuration page (/admin/structure/block).
2. Find the "User Comment Statistics" block and place it in the desired region of your theme.
3. Configure the block options according to your needs.