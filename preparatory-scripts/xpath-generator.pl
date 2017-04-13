#!/usr/bin/perl
# XPathGenerator.pl
# Alpino-XML XPath Generator

# version 1.7 date: 10.06.2015  bug fix (@number)
# version 1.6 date: 15.12.2014  bug fix (ignore not-function if word order is checked)
# version 1.5 date: 14.10.2014  RELEASED WITH GrETEL2.0
# written by Vincent Vandeghinste and Liesbeth Augustinus (c) 2014
# for the GrETEL2.0 project

# script converts an XML tree into an XPath expression

############################################################################
# argument: -xml: path to xml-subtree
# options: -order/-o: word order is important
#          -r: exclude root node from the XPath expression
#          -version/-v: script details
#          -in: attributes to include (comma-separated list)
#          -ex: attributes to exclude (comma-separated list)

############################################################################

use XML::Twig;

my ( $inputxml, $order ) = @ARGV;

if ( $order eq 'false' ) {
    $order = 0;
}
else {
    $order = 1;
}

my $twig = XML::Twig->new( 'pretty_print' => 'indented' );

$twig->parsefile($inputxml);

my $root = $twig->root;
my $subtree;

if ( $root->get_xpath('/alpino_ds') )
{    # for ALPINO XML, leave out the alpino_ds node
    $subtree = $root->first_child;
}
else {
    $subtree = $root;    # start at root node
}

# generate XPath expression
my $topxpath = GetXPath($subtree);
$xpath = ProcessTree( $subtree );

my $orderString = $order ? ' and not(.//node[position() < last()][number(@begin) > following-sibling::node/number(@begin)])' : '';

if ( $xpath && $topxpath ) {    # if more than one node is selected
    $xpath = '//' . $topxpath . $orderString . ' and ' . $xpath . ']';
}

elsif ( $xpath && !$topxpath ) {
    $xpath = '//*[' . $xpath . ']';
}

elsif ( !$xpath && $topxpath ) {
    $xpath = '//' . $topxpath . ']';    # if only one node is selected
}

else {
    print "ERROR: no XPath expression could be generated.\n";
}

if ( $xpath =~ /not/ ) {                # exclude nodes using not-function
    $xpath =~ s/\sand\s\@not=".*?"//g;
}

print $xpath;

sub ProcessTree {
    my ( $tree ) = @_;
    my ( $xpath, $nextpath, $childxpath );
    my @children = $tree->children;
    my ( @childxpaths, %COUNTS, %ALREADY );
    if ( @children > 0 ) {
        foreach (@children) {
            $childxpath = GetXPath($_);

            if ($childxpath) {
                $lower = &ProcessTree( $_ );
                if ($lower) {
                    $childxpath .= ' and ' . $lower . ']';
                }
                else {
                    $childxpath .= ']';

                    if ( $childxpath =~ /not/ )
                    {    # exclude nodes using not-function
                        $childxpath = 'not(' . $childxpath . ')';
                        $childxpath =~ s/\sand\s\@not=".*?"//;
                    }

                }
                $COUNTS{$childxpath}++;
                push( @childxpaths, $childxpath );

            }
        }
        if (@childxpaths) {
            for ( $i = 0 ; $i < @childxpaths ; $i++ ) {

                ## ADD COUNT FUNCTION
                if ( $COUNTS{ $childxpaths[$i] } > 1 ) {
                    $childxpaths[$i] =
                        'count('
                      . $childxpaths[$i] . ') > '
                      . ( $COUNTS{ $childxpaths[$i] } - 1 );
                    $dummy;
                }
                ## REMOVE DOUBLE DAUGHTERS
                if ( $ALREADY{ $childxpaths[$i] } ) {
                    splice( @childxpaths, $i, 1 );
                    $i--;
                }
                else {
                    $ALREADY{ $childxpaths[$i] } = 1;
                }

            }
            $xpath = join( ' and ', @childxpaths );
        }
        else {
            #die "not implemented yet\n";
            return undef;
        }
    }
    return $xpath;
}

sub FindNextTerminalToCompare {
    my ($tree) = @_;
    my ( $path, $xpath, $next_terminal );
    if ( $next_sibling = $tree->next_sibling ) {
        $path = "../";
        ( $next_terminal, $xpath ) = &FindNextLeafNode($next_sibling);
        $path = $path . $xpath;
        if ( $path =~ /begin/ ) {

            # $path='number('.$path.')';
            $path =~ s/\@begin/number\(\@begin\)/;
        }
    }
    else {
        # go up the tree to find next sibling
        my $parent = $tree->parent;
        if ($parent) {
            ( $next_terminal, $nextpath ) = &FindNextTerminalToCompare($parent);
            unless ($nextpath) {
                return undef;
            }
            $path = "../" . $nextpath;
        }
        else {
            return undef;
        }
    }
    return ( $next_terminal, $path );
}

sub FindNextLeafNode {
    my ($node) = @_;
    my @children = $node->children;
    my $childpath;
    my $xpath = GetXPath($node) . ']';

    if ( @children > 0 ) {
        ( $node, $childpath ) = FindNextLeafNode( $children[0] );
        $xpath .= "/" . $childpath;
        return ( $node, $xpath );
    }
    else {
        my $path = $xpath . '/@begin';
        return ( $node, $path );
    }
}

sub GetXPath {
    my ($tree) = @_;
    my $att = $tree->{'att'};
    my @atts;

    foreach ( keys %$att )
    {    # all attributes are included in the XPath expression...
        unless (/postag|begin|end/) {    # ...except these ones
            push( @atts, "@" . $_ . "=\"" . $$att{$_} . "\"" );
        }
    }

    my $xstring;

    if ( !@atts ) {                      # no matching attributes found
        return undef;
    }

    else {                               # one or more attributes found
        my $string = join( " and ", @atts );
        $xstring = "node[" . $string;

        return $xstring;
    }

}
