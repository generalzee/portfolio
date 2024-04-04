<?php /*Template Name: Investor Relations
* This is a sanitized template made for a real client. Some code has been altered for security reasons.
*/ 
get_header(); ?>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div id="content" rel="main">

            <?php if( have_rows('page_sections') ): ?>
                <?php $i = 0; while ( have_rows('page_sections') ) : the_row(); $i++; if ($i == 1) : ?>
                    <section>
                        <h1><?php the_sub_field('section_title'); ?></h1>
                        <?php if (get_sub_field('section_content')) { ?>
                        <div class="section-content content-centered">
                            <?php the_sub_field('section_content'); ?>
                        </div>
                        <?php } ?>
                    </section>
                <?php endif; endwhile; ?>
            <?php else: ?>
                <section>
                    <h1><?php the_title(); ?></h1>
                    <div class="section-content">
                        <?php the_content(); ?>
                    </div><!--End section-content-->
                </section>
            <?php endif; ?>

            <section>
                <h1><?php the_field('xblr_title'); ?></h1>
                <div class="section-content">
                    <?php if( have_rows('xbrl_year', 21) ): ?>
                        <?php while( have_rows('xbrl_year', 21) ): the_row(); ?>
                            <div class="xbrl-year-block clearfix">
                                <h2><?php the_sub_field('year'); ?></h2>
                                <?php if( have_rows('xbrl_quarter') ): ?>
                                <div class="xbrl-content">
                                    <?php while( have_rows('xbrl_quarter') ): the_row(); ?>
                                    <div class="xbrl-content-block">
                                        <h3><?php the_sub_field('quarter_title'); ?></h3>
                                        <?php if( have_rows('xbrl_documents') ): ?>
                                        <ul>
                                        <?php while( have_rows('xbrl_documents') ): the_row(); ?>
                                            <li><a href="<?php the_sub_field('document'); ?>"><?php the_sub_field('title'); ?></a></li>
                                        <?php endwhile; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </div><!--End xbrl-content-block-->
                                    <?php endwhile; ?>
                                </div>
                                <?php endif; ?>
                            </div><!--End xbrl-year-block-->
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div><!--End section-content-->
            </section>

            <?php if( have_rows('page_sections') ): ?>
                <?php $i = 0; while ( have_rows('page_sections') ) : the_row(); $i++; if ($i != 1) : ?>
                    <section>
                        <h1><?php the_sub_field('section_title'); ?></h1>
                        <?php if (get_sub_field('section_content')) { ?>
                        <div class="section-content content-centered">
                            <?php the_sub_field('section_content'); ?>
                        </div>
                        <?php } ?>
                    </section>
                <?php endif; endwhile; ?>
            <?php else: ?>
                <section>
                    <h1><?php the_title(); ?></h1>
                    <div class="section-content">
                        <?php the_content(); ?>
                    </div><!--End section-content-->
                </section>
            <?php endif; ?>

            <?php if( have_rows('distribution_history_sections') ): ?>
            <section>
                <h1><?php the_field('distribution_history_title'); ?></h1>
                <div class="section-content distribution-history">
                    <?php while( have_rows('distribution_history_sections') ): the_row(); ?>
                    <div class="distribution-history-section">
                        <h3><?php the_sub_field('distribution_history_year'); ?></h3>
                        <?php if( have_rows('distribution_history_table') ): ?>
                            <table cellspacing="0" cellpadding="0" class="dh-table">
                                <thead>
                                    <tr>
                                        <th width="20%">Record Date</th>
                                        <th width="20%"></th>
                                        <th width="20%">Paid Date</th>
                                        <th width="20%"></th>
                                        <th width="20%">$ Per Share</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while( have_rows('distribution_history_table') ): the_row(); ?>
                                    <tr>
                                        <td><?php the_sub_field('record_date'); ?></td>
                                        <td></td>
                                        <td><?php the_sub_field('paid_date'); ?></td>
                                        <td></td>
                                        <td><?php the_sub_field('$_per_share'); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        <p>Total <?php the_sub_field('distribution_year'); ?> &ndash; <?php the_sub_field('distribution_total'); ?></p>
                    </div>
                    <?php endwhile; ?>
                </div><!--End section-content-->
            </section>
            <?php endif; ?>

        </div>

        <?php endwhile; else : ?>
            <p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
        <?php endif; ?>

<?php get_footer(); ?>
