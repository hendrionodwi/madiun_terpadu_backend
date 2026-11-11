<?php

namespace App\Policies;

use App\Models\EducationArticle;
use App\Models\User;

class EducationArticlePolicy
{
    /**
     * Determine whether the user can view any education articles in CMS.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can view the education article in CMS.
     */
    public function view(User $user, EducationArticle $educationArticle): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can create education articles.
     */
    public function create(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can update the education article.
     */
    public function update(User $user, EducationArticle $educationArticle): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can delete the education article.
     */
    public function delete(User $user, EducationArticle $educationArticle): bool
    {
        return $user->isAdminDlh();
    }
}
